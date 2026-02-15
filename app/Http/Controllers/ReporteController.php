<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\UsoMesa;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    // Reporte de ventas
    public function ventas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        
        $ventas = Venta::pagadas()
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['usuario', 'cliente'])
            ->get();
        
        $resumen = [
            'total_ventas' => $ventas->sum('total'),
            'cantidad_ventas' => $ventas->count(),
            'promedio_venta' => $ventas->count() > 0 ? $ventas->sum('total') / $ventas->count() : 0,
            'total_descuentos' => $ventas->sum('descuento')
        ];
        
        // Ventas por día
        $ventasPorDia = $ventas->groupBy(function($venta) {
            return $venta->created_at->format('Y-m-d');
        })->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum('total')
            ];
        });
        
        // Ventas por método de pago
        $ventasPorMetodo = $ventas->groupBy('metodo_pago')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum('total')
            ];
        });
        
        return view('reportes.ventas', compact('ventas', 'resumen', 'ventasPorDia', 'ventasPorMetodo', 'fechaInicio', 'fechaFin'));
    }

    // Reporte de productos
    public function productos(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        
        // Productos más vendidos
        $productosMasVendidos = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'pagada')
            ->whereBetween('ventas.created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(detalle_ventas.cantidad) as cantidad_vendida'),
                DB::raw('SUM(detalle_ventas.subtotal) as total_ventas')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('cantidad_vendida')
            ->take(20)
            ->get();
        
        // Productos con stock bajo
        $productosStockBajo = Producto::stockBajo()
            ->with('categoria')
            ->get();
        
        return view('reportes.productos', compact('productosMasVendidos', 'productosStockBajo', 'fechaInicio', 'fechaFin'));
    }

    // Reporte de uso de mesas
    public function mesas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        
        $usos = UsoMesa::where('estado', 'finalizada')
            ->whereBetween('hora_inicio', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->with(['mesa', 'cliente'])
            ->get();
        
        $resumen = [
            'total_usos' => $usos->count(),
            'minutos_totales' => $usos->sum('minutos_totales'),
            'ingresos_totales' => $usos->sum('total'),
            'promedio_minutos' => $usos->count() > 0 ? $usos->sum('minutos_totales') / $usos->count() : 0
        ];
        
        // Uso por mesa
        $usoPorMesa = $usos->groupBy('mesa_id')->map(function($grupo) {
            return [
                'mesa' => $grupo->first()->mesa->nombre,
                'usos' => $grupo->count(),
                'minutos' => $grupo->sum('minutos_totales'),
                'ingresos' => $grupo->sum('total')
            ];
        });
        
        return view('reportes.mesas', compact('usos', 'resumen', 'usoPorMesa', 'fechaInicio', 'fechaFin'));
    }

    // Reporte de clientes
    public function clientes(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));
        
        // Clientes más frecuentes
        $clientesFrecuentes = Cliente::activos()
            ->withCount(['usosMesas' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('hora_inicio', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            }])
            ->withSum(['ventas' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                      ->where('estado', 'pagada');
            }], 'total')
            ->having('usos_mesas_count', '>', 0)
            ->orderByDesc('usos_mesas_count')
            ->take(20)
            ->get();
        
        // Nuevos clientes
        $nuevosClientes = Cliente::whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->count();
        
        return view('reportes.clientes', compact('clientesFrecuentes', 'nuevosClientes', 'fechaInicio', 'fechaFin'));
    }

    // Dashboard de estadísticas
    public function dashboard()
    {
        // Estadísticas del mes actual
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        
        $stats = [
            'ventas_mes' => Venta::pagadas()
                ->whereBetween('created_at', [$inicioMes, $finMes])
                ->sum('total'),
            'cantidad_ventas_mes' => Venta::pagadas()
                ->whereBetween('created_at', [$inicioMes, $finMes])
                ->count(),
            'usos_mes' => UsoMesa::where('estado', 'finalizada')
                ->whereBetween('hora_inicio', [$inicioMes, $finMes])
                ->count(),
            'ingresos_mesas_mes' => UsoMesa::where('estado', 'finalizada')
                ->whereBetween('hora_inicio', [$inicioMes, $finMes])
                ->sum('total'),
            'nuevos_clientes_mes' => Cliente::whereBetween('created_at', [$inicioMes, $finMes])->count()
        ];
        
        // Comparativa con mes anterior
        $inicioMesAnterior = now()->subMonth()->startOfMonth();
        $finMesAnterior = now()->subMonth()->endOfMonth();
        
        $statsMesAnterior = [
            'ventas' => Venta::pagadas()
                ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
                ->sum('total'),
            'cantidad_ventas' => Venta::pagadas()
                ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
                ->count()
        ];
        
        return view('reportes.dashboard', compact('stats', 'statsMesAnterior'));
    }
}
