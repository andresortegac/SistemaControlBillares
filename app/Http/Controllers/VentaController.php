<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\UsoMesa;
use App\Models\Cliente;
use App\Models\Mesa;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with(['usuario', 'cliente']);
        
        // Filtros
        if ($request->has('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }
        
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        $ventas = $query->latest()->paginate(20);
        
        // Resumen
        $totalVentas = Venta::hoy()->pagadas()->sum('total');
        $cantidadVentas = Venta::hoy()->pagadas()->count();
        
        return view('ventas.index', compact('ventas', 'totalVentas', 'cantidadVentas'));
    }

    public function create(Request $request)
    {
        $productos = Producto::activos()->conStock()->get();
        $mesas = Mesa::where('estado', 'ocupada')->get();
        $clientes = Cliente::activos()->get();
        
        // Si viene de finalizar uso de mesa
        $usoMesa = null;
        if ($request->has('uso_mesa_id')) {
            $usoMesa = UsoMesa::with('mesa', 'cliente')->find($request->uso_mesa_id);
        }
        
        return view('ventas.create', compact('productos', 'mesas', 'clientes', 'usoMesa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'tipo' => 'required|in:productos,mesa,mixta',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'efectivo_recibido' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Calcular totales
            $subtotal = 0;
            foreach ($validated['productos'] as $prod) {
                $subtotal += $prod['cantidad'] * $prod['precio'];
            }

            $descuento = $validated['descuento'] ?? 0;
            $total = $subtotal - $descuento;

            // Calcular cambio si es efectivo
            $efectivoRecibido = $validated['efectivo_recibido'] ?? null;
            $cambio = null;
            if ($efectivoRecibido && $efectivoRecibido >= $total) {
                $cambio = $efectivoRecibido - $total;
            }

            // Crear venta
            $venta = Venta::create([
                'user_id' => auth()->id(),
                'cliente_id' => $validated['cliente_id'] ?? null,
                'tipo' => $validated['tipo'],
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'metodo_pago' => $validated['metodo_pago'],
                'efectivo_recibido' => $efectivoRecibido,
                'cambio' => $cambio,
                'estado' => 'pagada',
                'notas' => $validated['notas'] ?? null
            ]);

            // Crear detalles y actualizar stock
            foreach ($validated['productos'] as $prod) {
                $producto = Producto::find($prod['id']);
                
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $prod['id'],
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio'],
                    'subtotal' => $prod['cantidad'] * $prod['precio'],
                    'tipo_item' => 'producto'
                ]);

                // Descontar stock
                $producto->stock -= $prod['cantidad'];
                $producto->save();
            }

            // Si hay uso de mesa, vincularlo
            if ($request->has('uso_mesa_id')) {
                $usoMesa = UsoMesa::find($request->uso_mesa_id);
                if ($usoMesa) {
                    $usoMesa->venta_id = $venta->id;
                    $usoMesa->save();
                    
                    // Agregar detalle de mesa
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => null,
                        'cantidad' => 1,
                        'precio_unitario' => $usoMesa->total,
                        'subtotal' => $usoMesa->total,
                        'tipo_item' => 'mesa',
                        'descripcion' => 'Uso de ' . $usoMesa->mesa->nombre . ' - ' . $usoMesa->minutos_totales . ' minutos'
                    ]);
                }
            }

            // Agregar puntos al cliente si existe
            if ($venta->cliente_id) {
                $puntos = floor($total / 10); // 1 punto por cada $10
                $venta->cliente->agregarPuntos($puntos);
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', 'Venta realizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    public function show(Venta $venta)
    {
        $venta->load(['detalles.producto', 'cliente', 'usuario', 'usoMesa.mesa']);
        return view('ventas.show', compact('venta'));
    }

    public function destroy(Venta $venta)
    {
        if ($venta->estado === 'cancelada') {
            return redirect()->back()
                ->with('error', 'La venta ya está cancelada');
        }

        try {
            DB::beginTransaction();

            // Devolver stock
            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto_id) {
                    $producto = Producto::find($detalle->producto_id);
                    if ($producto) {
                        $producto->stock += $detalle->cantidad;
                        $producto->save();
                    }
                }
            }

            // Si hay uso de mesa, revertir
            if ($venta->usoMesa) {
                $usoMesa = $venta->usoMesa;
                $usoMesa->estado = 'en_curso';
                $usoMesa->venta_id = null;
                $usoMesa->save();
                
                $usoMesa->mesa->estado = 'ocupada';
                $usoMesa->mesa->save();
            }

            $venta->estado = 'cancelada';
            $venta->save();

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta cancelada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }

    // Ticket de venta
    public function ticket(Venta $venta)
    {
        $venta->load(['detalles.producto', 'cliente', 'usuario']);
        return view('ventas.ticket', compact('venta'));
    }

    // Venta rápida de mesa
    public function ventaMesa(Request $request, Mesa $mesa)
    {
        $usoMesa = $mesa->usoActivo();
        
        if (!$usoMesa) {
            return redirect()->back()
                ->with('error', 'La mesa no tiene un uso activo');
        }

        $usoMesa->finalizar();

        return redirect()->route('ventas.create', ['uso_mesa_id' => $usoMesa->id]);
    }
}
