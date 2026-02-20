<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;
use App\Models\UsoMesa;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::with(['usos' => function($query) {
            $query->where('estado', 'en_curso');
        }, 'cuentaActiva'])->get();
        
        return view('mesas.index', compact('mesas'));
    }

    public function create()
    {
        return view('mesas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:mesas',
            'tipo' => 'required|in:pool,snooker,carambola',
            'precio_por_hora' => 'required|numeric|min:0',
            'estado' => 'nullable|in:disponible,ocupada,mantenimiento',
            'descripcion' => 'nullable|string'
        ]);

        Mesa::create($validated);

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa creada exitosamente');
    }

    public function show(Mesa $mesa)
    {
        $mesa->load(['usos' => function($query) {
            $query->latest()->take(10);
        }, 'usos.cliente', 'usos.usuario']);
        $productos = Producto::activos()->conStock()->orderBy('nombre')->get();

        return view('mesas.show', compact('mesa', 'productos'));
    }

    public function edit(Mesa $mesa)
    {
        return view('mesas.edit', compact('mesa'));
    }

    public function update(Request $request, Mesa $mesa)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:mesas,nombre,' . $mesa->id,
            'tipo' => 'required|in:pool,snooker,carambola',
            'precio_por_hora' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:disponible,ocupada,mantenimiento'
        ]);

        $mesa->update($validated);

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa actualizada exitosamente');
    }

    public function destroy(Mesa $mesa)
    {
        if ($mesa->usos()->whereIn('estado', ['en_curso', 'pausada'])->exists()) {
            return redirect()->route('mesas.index')
                ->with('error', 'No se puede eliminar una mesa con uso en curso o pausado');
        }

        DB::transaction(function () use ($mesa) {
            $mesa->usos()->delete();
            $mesa->delete();
        });

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa eliminada exitosamente');
    }

    // Iniciar uso de mesa
    public function iniciarUso(Request $request, Mesa $mesa)
    {
        if (!$mesa->estaDisponible()) {
            return redirect()->back()
                ->with('error', 'La mesa no está disponible');
        }

        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'notas' => 'nullable|string'
        ]);

        $uso = UsoMesa::create([
            'mesa_id' => $mesa->id,
            'cliente_id' => $validated['cliente_id'] ?? null,
            'user_id' => auth()->id(),
            'hora_inicio' => now(),
            'precio_hora' => $mesa->precio_por_hora,
            'estado' => 'en_curso',
            'notas' => $validated['notas'] ?? null
        ]);

        $mesa->estado = 'ocupada';
        $mesa->save();

        return redirect()->route('mesas.index')
            ->with('success', 'Uso de mesa iniciado');
    }

    // Finalizar uso de mesa
    public function finalizarUso(Mesa $mesa)
    {
        $uso = $mesa->usoActual();
        
        if (!$uso) {
            return redirect()->back()
                ->with('error', 'No hay uso activo o pausado para esta mesa');
        }

        $uso->finalizar();

        return redirect()->route('ventas.create', ['uso_mesa_id' => $uso->id])
            ->with('success', 'Uso finalizado. Proceda a cobrar.');
    }

    // Pausar uso de mesa
    public function pausarUso(Mesa $mesa)
    {
        $uso = $mesa->usoEnCurso();
        
        if (!$uso) {
            return redirect()->back()
                ->with('error', 'No hay uso en curso para esta mesa');
        }

        $uso->pausar();

        return redirect()->route('mesas.index')
            ->with('success', 'Uso de mesa pausado');
    }

    public function reanudarUso(Mesa $mesa)
    {
        $uso = $mesa->usoPausado();

        if (!$uso) {
            return redirect()->back()
                ->with('error', 'No hay uso pausado para esta mesa');
        }

        if ($mesa->usoEnCurso()) {
            return redirect()->back()
                ->with('error', 'La mesa ya tiene un uso en curso');
        }

        $uso->reanudar();

        return redirect()->route('mesas.index')
            ->with('success', 'Uso de mesa reanudado');
    }

    // Cambiar estado de mesa
    public function cambiarEstado(Request $request, Mesa $mesa)
    {
        $validated = $request->validate([
            'estado' => 'required|in:disponible,mantenimiento,reservada'
        ]);

        if ($mesa->estado === 'ocupada' && $validated['estado'] !== 'ocupada') {
            return redirect()->back()
                ->with('error', 'No se puede cambiar el estado de una mesa ocupada. Finalice el uso primero.');
        }

        $mesa->estado = $validated['estado'];
        $mesa->save();

        return redirect()->route('mesas.index')
            ->with('success', 'Estado de mesa actualizado');
    }

    public function cerrarPartida(Request $request, Mesa $mesa)
    {
        $validated = $request->validate([
            'valor_ronda' => 'required|numeric|min:0',
            'perdedor_equipo' => 'required|in:1,2',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'efectivo_recibido' => 'nullable|numeric|min:0',
            'jugadores' => 'required|array|size:4',
            'jugadores.*.nombre' => 'required|string|max:80',
            'jugadores.*.equipo' => 'required|in:1,2',
            'consumos' => 'nullable|array',
            'consumos.*.jugador_index' => 'required_with:consumos|integer|min:0|max:3',
            'consumos.*.producto_id' => 'required_with:consumos|exists:productos,id',
            'consumos.*.cantidad' => 'required_with:consumos|integer|min:1',
        ]);

        $jugadores = $validated['jugadores'];
        $equipo1 = collect($jugadores)->where('equipo', '1');
        $equipo2 = collect($jugadores)->where('equipo', '2');

        if ($equipo1->count() !== 2 || $equipo2->count() !== 2) {
            return back()->withInput()->with('error', 'Debe haber exactamente 2 jugadores por equipo.');
        }

        $consumos = collect($validated['consumos'] ?? []);
        $valorRonda = (float) $validated['valor_ronda'];
        $perdedorEquipo = (int) $validated['perdedor_equipo'];

        $cantidadesPorProducto = $consumos
            ->groupBy('producto_id')
            ->map(fn ($lineas) => $lineas->sum('cantidad'));

        $productos = Producto::whereIn('id', $cantidadesPorProducto->keys())->get()->keyBy('id');

        foreach ($cantidadesPorProducto as $productoId => $cantidad) {
            $producto = $productos->get($productoId);
            if (!$producto || $producto->stock < $cantidad) {
                return back()->withInput()->with('error', "Stock insuficiente para el producto #{$productoId}.");
            }
        }

        $roundShare = [];
        for ($i = 0; $i < 4; $i++) {
            $equipoJugador = (int) $jugadores[$i]['equipo'];
            $roundShare[$i] = $equipoJugador === $perdedorEquipo ? ($valorRonda / 2) : 0;
        }

        $totalesJugador = array_fill(0, 4, 0);
        $subtotalProductos = 0;
        foreach ($consumos as $consumo) {
            $producto = $productos->get($consumo['producto_id']);
            if (!$producto) {
                continue;
            }
            $lineTotal = (float) $producto->precio_venta * (int) $consumo['cantidad'];
            $subtotalProductos += $lineTotal;
            $totalesJugador[(int) $consumo['jugador_index']] += $lineTotal;
        }

        for ($i = 0; $i < 4; $i++) {
            $totalesJugador[$i] += $roundShare[$i];
        }

        $subtotal = $subtotalProductos + $valorRonda;

        DB::transaction(function () use (
            $mesa,
            $validated,
            $jugadores,
            $consumos,
            $productos,
            $totalesJugador,
            $subtotal,
            $subtotalProductos,
            $valorRonda,
            $perdedorEquipo
        ) {
            $venta = Venta::create([
                'user_id' => auth()->id(),
                'cliente_id' => null,
                'tipo' => 'mixta',
                'subtotal' => $subtotal,
                'descuento' => 0,
                'total' => $subtotal,
                'metodo_pago' => $validated['metodo_pago'],
                'efectivo_recibido' => $validated['efectivo_recibido'] ?? null,
                'cambio' => null,
                'estado' => 'pagada',
                'notas' => json_encode([
                    'mesa_id' => $mesa->id,
                    'jugadores' => $jugadores,
                    'totales_jugador' => $totalesJugador,
                    'subtotal_productos' => $subtotalProductos,
                    'valor_ronda' => $valorRonda,
                    'perdedor_equipo' => $perdedorEquipo,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            foreach ($consumos as $consumo) {
                $producto = $productos->get($consumo['producto_id']);
                if (!$producto) {
                    continue;
                }

                $cantidad = (int) $consumo['cantidad'];
                $jugadorIndex = (int) $consumo['jugador_index'];
                $jugadorNombre = $jugadores[$jugadorIndex]['nombre'] ?? 'Jugador';

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $cantidad * (float) $producto->precio_venta,
                    'tipo_item' => 'producto',
                    'descripcion' => 'Consumo de ' . $jugadorNombre . ' en ' . $mesa->nombre,
                ]);

                $producto->stock -= $cantidad;
                $producto->save();
            }

            DetalleVenta::create([
                'venta_id' => $venta->id,
                'producto_id' => null,
                'cantidad' => 1,
                'precio_unitario' => $valorRonda,
                'subtotal' => $valorRonda,
                'tipo_item' => 'tiempo',
                'descripcion' => 'Ronda en ' . $mesa->nombre . ' (equipo perdedor: ' . $perdedorEquipo . ')',
            ]);

            $usoActual = $mesa->usoActual();
            if ($usoActual) {
                $usoActual->hora_fin = now();
                $usoActual->minutos_totales = $usoActual->getTiempoTranscurrido()['total_minutos'];
                $usoActual->total = 0;
                $usoActual->estado = 'finalizada';
                $usoActual->venta_id = $venta->id;
                $usoActual->save();
            }

            $mesa->estado = 'disponible';
            $mesa->save();
        });

        return redirect()->route('mesas.index')
            ->with('success', 'Partida finalizada y venta registrada correctamente.');
    }
}
