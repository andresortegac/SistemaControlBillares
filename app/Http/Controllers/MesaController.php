<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;
use App\Models\UsoMesa;
use App\Models\Cliente;
use App\Models\Configuracion;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::with(['usos' => function($query) {
            $query->where('estado', 'en_curso');
        }])->get();
        
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
        
        return view('mesas.show', compact('mesa'));
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
            'estado' => 'required|in:disponible,ocupada,mantenimiento,reservada'
        ]);

        $mesa->update($validated);

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa actualizada exitosamente');
    }

    public function destroy(Mesa $mesa)
    {
        if ($mesa->usos()->count() > 0) {
            return redirect()->route('mesas.index')
                ->with('error', 'No se puede eliminar la mesa porque tiene registros de uso');
        }

        $mesa->delete();

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
        $uso = $mesa->usoActivo();
        
        if (!$uso) {
            return redirect()->back()
                ->with('error', 'No hay uso activo para esta mesa');
        }

        $uso->finalizar();

        return redirect()->route('ventas.create', ['uso_mesa_id' => $uso->id])
            ->with('success', 'Uso finalizado. Proceda a cobrar.');
    }

    // Pausar uso de mesa
    public function pausarUso(Mesa $mesa)
    {
        $uso = $mesa->usoActivo();
        
        if (!$uso) {
            return redirect()->back()
                ->with('error', 'No hay uso activo para esta mesa');
        }

        $uso->pausar();

        return redirect()->route('mesas.index')
            ->with('success', 'Uso de mesa pausado');
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
}
