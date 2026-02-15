<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\UsoMesa;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas del día
        $ventasHoy = Venta::hoy()->pagadas()->count();
        $ingresosHoy = Venta::hoy()->pagadas()->sum('total');
        
        // Mesas
        $mesasOcupadas = Mesa::where('estado', 'ocupada')->count();
        $mesasDisponibles = Mesa::where('estado', 'disponible')->count();
        $totalMesas = Mesa::count();
        
        // Productos con stock bajo
        $productosStockBajo = Producto::stockBajo()->with('categoria')->get();
        
        // Ventas recientes
        $ventasRecientes = Venta::with(['usuario', 'cliente'])
            ->latest()
            ->take(10)
            ->get();
        
        // Mesas en uso actualmente
        $mesasEnUso = UsoMesa::enCurso()
            ->with(['mesa', 'cliente'])
            ->get();
        
        // Estadísticas de la semana
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        
        $ventasSemana = Venta::pagadas()
            ->whereBetween('created_at', [$inicioSemana, $finSemana])
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('fecha')
            ->get();
        
        // Total clientes
        $totalClientes = Cliente::activos()->count();
        
        // Productos más vendidos hoy
        $productosTop = \App\Models\DetalleVenta::whereHas('venta', function($q) {
                $q->whereDate('created_at', today())->where('estado', 'pagada');
            })
            ->with('producto')
            ->selectRaw('producto_id, SUM(cantidad) as cantidad_vendida')
            ->groupBy('producto_id')
            ->orderByDesc('cantidad_vendida')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'ventasHoy',
            'ingresosHoy',
            'mesasOcupadas',
            'mesasDisponibles',
            'totalMesas',
            'productosStockBajo',
            'ventasRecientes',
            'mesasEnUso',
            'ventasSemana',
            'totalClientes',
            'productosTop'
        ));
    }
}
