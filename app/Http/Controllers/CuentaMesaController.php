<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbrirCuentaMesaRequest;
use App\Http\Requests\CerrarCuentaMesaRequest;
use App\Http\Requests\RegistrarPagoRequest;
use App\Http\Requests\RegistrarRondaRequest;
use App\Http\Requests\StoreConsumoMesaRequest;
use App\Http\Requests\StoreJugadorMesaRequest;
use App\Models\CuentaMesa;
use App\Models\JugadorMesa;
use App\Models\Mesa;
use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CuentaMesaController extends Controller
{
    public function store(AbrirCuentaMesaRequest $request, Mesa $mesa)
    {
        if ($mesa->estado !== 'disponible') {
            return back()->with('error', 'La mesa no está disponible para abrir cuenta.');
        }

        if ($mesa->cuentaActiva()->exists()) {
            return back()->with('error', 'La mesa ya tiene una cuenta activa.');
        }

        $cuenta = DB::transaction(function () use ($request, $mesa) {
            $cuenta = CuentaMesa::create([
                'mesa_id' => $mesa->id,
                'abierta_por' => auth()->id(),
                'estado' => 'activa',
                'abierta_en' => now(),
                'notas' => $request->validated('notas'),
            ]);

            $mesa->estado = 'ocupada';
            $mesa->save();

            return $cuenta;
        });

        return redirect()->route('cuentas_mesa.show', $cuenta)
            ->with('success', 'Cuenta de mesa abierta correctamente.');
    }

    public function show(CuentaMesa $cuentaMesa)
    {
        $cuentaMesa->load([
            'mesa',
            'jugadores' => fn ($query) => $query->orderBy('id'),
            'movimientos' => fn ($query) => $query->with('jugadorMesa')->latest('id'),
        ]);

        $saldos = $cuentaMesa->jugadores->mapWithKeys(
            fn (JugadorMesa $jugador) => [$jugador->id => $jugador->saldo()]
        );

        return view('cuentas_mesa.show', [
            'cuentaMesa' => $cuentaMesa,
            'saldos' => $saldos,
            'consumoPendiente' => $cuentaMesa->consumoPendiente(),
            'saldoTotal' => $cuentaMesa->saldoTotalJugadores(),
            'productos' => Producto::activos()->conStock()->orderBy('nombre')->get(),
        ]);
    }

    public function storeJugador(StoreJugadorMesaRequest $request, CuentaMesa $cuentaMesa)
    {
        if ($cuentaMesa->estado !== 'activa') {
            return back()->with('error', 'No se pueden agregar jugadores en una cuenta cerrada.');
        }

        $cuentaMesa->jugadores()->create($request->validated());

        return back()->with('success', 'Jugador agregado a la cuenta.');
    }

    public function inactivarJugador(CuentaMesa $cuentaMesa, JugadorMesa $jugadorMesa)
    {
        if ($jugadorMesa->cuenta_mesa_id !== $cuentaMesa->id) {
            return back()->with('error', 'El jugador no pertenece a esta cuenta.');
        }

        if (!$jugadorMesa->activo) {
            return back()->with('error', 'El jugador ya está inactivo.');
        }

        $jugadorMesa->activo = false;
        $jugadorMesa->inactivo_en = now();
        $jugadorMesa->save();

        return back()->with('success', 'Jugador marcado como inactivo. Su saldo se mantiene pendiente.');
    }

    public function storeConsumo(StoreConsumoMesaRequest $request, CuentaMesa $cuentaMesa)
    {
        if ($cuentaMesa->estado !== 'activa') {
            return back()->with('error', 'No se pueden registrar consumos en una cuenta cerrada.');
        }

        $validated = $request->validated();

        if (!empty($validated['producto_id'])) {
            try {
                DB::transaction(function () use ($cuentaMesa, $validated) {
                    $producto = Producto::lockForUpdate()->findOrFail($validated['producto_id']);
                    $cantidad = (int) ($validated['cantidad'] ?? 1);

                    if ($producto->stock < $cantidad) {
                        throw new \RuntimeException("Stock insuficiente para {$producto->nombre}.");
                    }

                    $monto = (float) $producto->precio_venta * $cantidad;
                    $descripcion = $validated['descripcion']
                        ?? ($producto->nombre . ' x ' . $cantidad);

                    Movimiento::create([
                        'cuenta_mesa_id' => $cuentaMesa->id,
                        'jugador_mesa_id' => null,
                        'tipo' => Movimiento::TIPO_CONSUMO,
                        'monto' => $monto,
                        'descripcion' => $descripcion,
                        'meta' => [
                            'producto_id' => $producto->id,
                            'producto_nombre' => $producto->nombre,
                            'cantidad' => $cantidad,
                            'precio_unitario' => (float) $producto->precio_venta,
                        ],
                    ]);

                    $producto->stock -= $cantidad;
                    $producto->save();
                });
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        } else {
            Movimiento::create([
                'cuenta_mesa_id' => $cuentaMesa->id,
                'jugador_mesa_id' => null,
                'tipo' => Movimiento::TIPO_CONSUMO,
                'monto' => $validated['monto'],
                'descripcion' => $validated['descripcion'],
            ]);
        }

        return back()->with('success', 'Consumo general registrado.');
    }

    public function registrarRonda(RegistrarRondaRequest $request, CuentaMesa $cuentaMesa)
    {
        if ($cuentaMesa->estado !== 'activa') {
            return back()->with('error', 'La cuenta está cerrada.');
        }

        $perdedoresIds = collect($request->validated('perdedores'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($perdedoresIds->isEmpty()) {
            return back()->with('error', 'Debe seleccionar al menos un perdedor para la ronda.');
        }

        try {
            DB::transaction(function () use ($cuentaMesa, $perdedoresIds, $request) {
                $jugadoresPerdedores = $cuentaMesa->jugadores()
                    ->whereIn('id', $perdedoresIds)
                    ->activos()
                    ->lockForUpdate()
                    ->get();

                if ($jugadoresPerdedores->count() !== $perdedoresIds->count()) {
                    throw new \RuntimeException('Seleccione solo jugadores activos de esta cuenta.');
                }

                $consumosPendientes = $cuentaMesa->movimientos()
                    ->where('tipo', Movimiento::TIPO_CONSUMO)
                    ->whereNull('jugador_mesa_id')
                    ->whereNull('liquidado_en')
                    ->lockForUpdate()
                    ->get();

                $totalPendiente = (float) $consumosPendientes->sum('monto');

                if ($totalPendiente <= 0) {
                    throw new \RuntimeException('No hay consumo pendiente para repartir en esta ronda.');
                }

                $loteId = (string) Str::uuid();
                $cantidadPerdedores = $jugadoresPerdedores->count();
                $cargoBase = round($totalPendiente / $cantidadPerdedores, 2);
                $restante = round($totalPendiente - ($cargoBase * $cantidadPerdedores), 2);
                $ultimoIndice = $cantidadPerdedores - 1;

                foreach ($jugadoresPerdedores->values() as $indice => $jugador) {
                    $montoCargo = $cargoBase + ($indice === $ultimoIndice ? $restante : 0);

                    Movimiento::create([
                        'cuenta_mesa_id' => $cuentaMesa->id,
                        'jugador_mesa_id' => $jugador->id,
                        'tipo' => Movimiento::TIPO_CARGO_PERDEDOR,
                        'monto' => $montoCargo,
                        'descripcion' => $request->validated('detalle') ?: 'Cargo por ronda perdida',
                        'lote_id' => $loteId,
                        'meta' => [
                            'consumo_repartido' => $totalPendiente,
                            'perdedores' => $perdedoresIds->all(),
                        ],
                    ]);
                }

                $cuentaMesa->movimientos()
                    ->whereIn('id', $consumosPendientes->pluck('id'))
                    ->update([
                        'liquidado_en' => now(),
                        'lote_id' => $loteId,
                        'meta' => json_encode([
                            'repartido_a' => $perdedoresIds->all(),
                            'detalle' => $request->validated('detalle'),
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Ronda registrada. El consumo pendiente fue dividido entre los perdedores.');
    }

    public function registrarPago(RegistrarPagoRequest $request, CuentaMesa $cuentaMesa, JugadorMesa $jugadorMesa)
    {
        if ($jugadorMesa->cuenta_mesa_id !== $cuentaMesa->id) {
            return back()->with('error', 'El jugador no pertenece a esta cuenta.');
        }

        $saldoActual = $jugadorMesa->saldo();
        $monto = (float) $request->validated('monto');

        if ($saldoActual <= 0) {
            return back()->with('error', 'El jugador no tiene saldo pendiente.');
        }

        if ($monto > $saldoActual) {
            return back()->with('error', 'El pago no puede superar el saldo pendiente del jugador.');
        }

        Movimiento::create([
            'cuenta_mesa_id' => $cuentaMesa->id,
            'jugador_mesa_id' => $jugadorMesa->id,
            'tipo' => Movimiento::TIPO_PAGO,
            'monto' => $monto,
            'descripcion' => $request->validated('descripcion') ?: 'Pago parcial de jugador',
        ]);

        return back()->with('success', 'Pago registrado.');
    }

    public function cerrar(CerrarCuentaMesaRequest $request, CuentaMesa $cuentaMesa)
    {
        if ($cuentaMesa->estado !== 'activa') {
            return back()->with('error', 'La cuenta ya está cerrada.');
        }

        $cuentaMesa->load('jugadores');

        if ($cuentaMesa->consumoPendiente() > 0) {
            return back()->with('error', 'Aún hay consumos pendientes por repartir.');
        }

        if ($cuentaMesa->saldoTotalJugadores() > 0) {
            return back()->with('error', 'No se puede cerrar: hay saldos pendientes de pago.');
        }

        if ($cuentaMesa->jugadores()->activos()->exists()) {
            return back()->with('error', 'Marque inactivos a los jugadores antes de cerrar la mesa.');
        }

        DB::transaction(function () use ($cuentaMesa, $request) {
            $cuentaMesa->estado = 'cerrada';
            $cuentaMesa->cerrada_en = now();
            $cuentaMesa->cerrada_por = auth()->id();
            $cuentaMesa->notas = $request->validated('notas') ?: $cuentaMesa->notas;
            $cuentaMesa->save();

            $mesa = $cuentaMesa->mesa;
            $mesa->estado = 'disponible';
            $mesa->save();
        });

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa cerrada correctamente. Todos los saldos están en cero.');
    }
}
