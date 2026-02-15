<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();
        
        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }
        
        if ($request->has('membresia')) {
            $query->where('tipo_membresia', $request->membresia);
        }
        
        $clientes = $query->latest()->paginate(20);
        
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'tipo_membresia' => 'required|in:ninguna,basica,premium,vip',
            'notas' => 'nullable|string'
        ]);

        Cliente::create($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['ventas' => function($query) {
            $query->latest()->take(10);
        }, 'usosMesas' => function($query) {
            $query->latest()->take(10);
        }]);
        
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'tipo_membresia' => 'required|in:ninguna,basica,premium,vip',
            'puntos_fidelidad' => 'integer|min:0',
            'saldo_favor' => 'numeric|min:0',
            'notas' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->activo = false;
        $cliente->save();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente desactivado exitosamente');
    }

    // API para buscar clientes
    public function buscar(Request $request)
    {
        $query = $request->get('q');
        
        $clientes = Cliente::activos()
            ->where(function($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('telefono', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'nombre', 'telefono', 'tipo_membresia']);
        
        return response()->json($clientes);
    }

    // Agregar puntos de fidelidad
    public function agregarPuntos(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'puntos' => 'required|integer|min:1'
        ]);

        $cliente->agregarPuntos($validated['puntos']);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Puntos agregados exitosamente');
    }
}
