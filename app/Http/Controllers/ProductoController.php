<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria');
        
        // Filtros
        if ($request->has('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }
        
        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }
        
        if ($request->has('stock_bajo')) {
            $query->stockBajo();
        }
        
        $productos = $query->latest()->paginate(20);
        $categorias = Categoria::activas()->get();
        
        return view('productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::activas()->get();
        return view('productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:20'
        ]);

        Producto::create($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'detalleVentas.venta']);
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::activas()->get();
        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo,' . $producto->id,
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:20',
            'activo' => 'boolean'
        ]);

        $producto->update($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->detalleVentas()->count() > 0) {
            $producto->activo = false;
            $producto->save();
            return redirect()->route('productos.index')
                ->with('success', 'Producto desactivado (tiene ventas asociadas)');
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente');
    }

    // API para obtener productos (para el punto de venta)
    public function buscar(Request $request)
    {
        $query = $request->get('q');
        
        $productos = Producto::activos()
            ->conStock()
            ->where(function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('codigo', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'codigo', 'nombre', 'precio_venta', 'stock']);
        
        return response()->json($productos);
    }

    // Ajustar stock
    public function ajustarStock(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'cantidad' => 'required|integer',
            'tipo' => 'required|in:entrada,salida',
            'motivo' => 'required|string'
        ]);

        if ($validated['tipo'] === 'entrada') {
            $producto->stock += $validated['cantidad'];
        } else {
            if ($producto->stock < $validated['cantidad']) {
                return redirect()->back()
                    ->with('error', 'Stock insuficiente para la salida');
            }
            $producto->stock -= $validated['cantidad'];
        }

        $producto->save();

        return redirect()->route('productos.index')
            ->with('success', 'Stock ajustado exitosamente');
    }
}
