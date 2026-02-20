<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGasto;
use App\Models\Gasto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        [$query, $fechaInicio, $fechaFin] = $this->buildFilteredQuery($request);

        $gastos = $query->latest('fecha')->latest('id')->paginate(20)->withQueryString();
        $categorias = CategoriaGasto::activas()->orderBy('grupo')->orderBy('nombre')->get();

        $totalGastos = (clone $query)->sum('monto');
        $cantidadGastos = (clone $query)->count();
        $promedioGasto = $cantidadGastos > 0 ? $totalGastos / $cantidadGastos : 0;
        $ingresosPeriodo = Venta::pagadas()
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->sum('total');

        return view('gastos.index', [
            'gastos' => $gastos,
            'categorias' => $categorias,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'resumen' => [
                'total_gastos' => $totalGastos,
                'cantidad_gastos' => $cantidadGastos,
                'promedio_gasto' => $promedioGasto,
                'ingresos' => $ingresosPeriodo,
                'utilidad' => $ingresosPeriodo - $totalGastos,
            ],
        ]);
    }

    public function create()
    {
        $this->authorizeAdmin();

        $categorias = CategoriaGasto::activas()->orderBy('grupo')->orderBy('nombre')->get()->groupBy('grupo');
        return view('gastos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'fecha' => 'required|date',
            'categoria_gasto_id' => 'required|exists:categorias_gasto,id',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'required|string|max:1000',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes_gastos', 'public');
        }

        Gasto::create([
            'fecha' => $validated['fecha'],
            'categoria_gasto_id' => $validated['categoria_gasto_id'],
            'monto' => $validated['monto'],
            'descripcion' => $validated['descripcion'],
            'comprobante_path' => $comprobantePath,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto registrado exitosamente');
    }

    public function show(Gasto $gasto)
    {
        $gasto->load(['categoria', 'usuario']);
        return view('gastos.show', compact('gasto'));
    }

    public function edit(Gasto $gasto)
    {
        $this->authorizeAdmin();

        $categorias = CategoriaGasto::activas()->orderBy('grupo')->orderBy('nombre')->get()->groupBy('grupo');
        return view('gastos.edit', compact('gasto', 'categorias'));
    }

    public function update(Request $request, Gasto $gasto)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'fecha' => 'required|date',
            'categoria_gasto_id' => 'required|exists:categorias_gasto,id',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'required|string|max:1000',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $comprobantePath = $gasto->comprobante_path;
        if ($request->hasFile('comprobante')) {
            if ($comprobantePath) {
                Storage::disk('public')->delete($comprobantePath);
            }
            $comprobantePath = $request->file('comprobante')->store('comprobantes_gastos', 'public');
        }

        $gasto->update([
            'fecha' => $validated['fecha'],
            'categoria_gasto_id' => $validated['categoria_gasto_id'],
            'monto' => $validated['monto'],
            'descripcion' => $validated['descripcion'],
            'comprobante_path' => $comprobantePath,
        ]);

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto actualizado exitosamente');
    }

    public function destroy(Gasto $gasto)
    {
        $this->authorizeAdmin();

        if ($gasto->comprobante_path) {
            Storage::disk('public')->delete($gasto->comprobante_path);
        }

        $gasto->delete();

        return redirect()->route('gastos.index')
            ->with('success', 'Gasto eliminado exitosamente');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$query, $fechaInicio, $fechaFin] = $this->buildFilteredQuery($request);
        $gastos = $query->latest('fecha')->latest('id')->get();

        $filename = 'reporte_gastos_' . $fechaInicio . '_a_' . $fechaFin . '.csv';

        return response()->streamDownload(function () use ($gastos) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fecha', 'Categoria', 'Grupo', 'Descripcion', 'Monto', 'Usuario']);

            foreach ($gastos as $gasto) {
                fputcsv($handle, [
                    $gasto->fecha->format('Y-m-d'),
                    $gasto->categoria->nombre,
                    $gasto->categoria->grupo,
                    $gasto->descripcion,
                    $gasto->monto,
                    $gasto->usuario->name,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function reporteMensual(Request $request)
    {
        $anio = (int) $request->get('anio', now()->year);
        if ($anio < 2000 || $anio > 2100) {
            $anio = now()->year;
        }

        $gastosMensuales = Gasto::selectRaw('MONTH(fecha) as mes, SUM(monto) as total')
            ->whereYear('fecha', $anio)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $ingresosMensuales = Venta::pagadas()
            ->selectRaw('MONTH(created_at) as mes, SUM(total) as total')
            ->whereYear('created_at', $anio)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $comparativaMensual = collect(range(1, 12))->map(function ($mes) use ($gastosMensuales, $ingresosMensuales) {
            $gastos = (float) ($gastosMensuales[$mes] ?? 0);
            $ingresos = (float) ($ingresosMensuales[$mes] ?? 0);

            return [
                'mes' => $mes,
                'nombre_mes' => now()->startOfYear()->month($mes)->translatedFormat('F'),
                'gastos' => $gastos,
                'ingresos' => $ingresos,
                'utilidad' => $ingresos - $gastos,
            ];
        });

        $topCategorias = Gasto::with('categoria')
            ->whereYear('fecha', $anio)
            ->get()
            ->groupBy(fn ($gasto) => $gasto->categoria->nombre)
            ->map(fn ($grupo) => $grupo->sum('monto'))
            ->sortDesc()
            ->take(10);

        return view('reportes.gastos', [
            'anio' => $anio,
            'comparativaMensual' => $comparativaMensual,
            'topCategorias' => $topCategorias,
            'totales' => [
                'gastos' => $comparativaMensual->sum('gastos'),
                'ingresos' => $comparativaMensual->sum('ingresos'),
                'utilidad' => $comparativaMensual->sum('utilidad'),
            ],
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->esAdmin(), 403, 'Solo admin puede realizar esta accion');
    }

    private function buildFilteredQuery(Request $request): array
    {
        $query = Gasto::with(['categoria', 'usuario']);

        $hoy = now()->toDateString();
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $periodo = $request->get('periodo');

        if (!$fechaInicio || !$fechaFin) {
            if ($periodo === 'anual') {
                $fechaInicio = now()->startOfYear()->toDateString();
                $fechaFin = now()->endOfYear()->toDateString();
            } elseif ($periodo === 'mensual') {
                $fechaInicio = now()->startOfMonth()->toDateString();
                $fechaFin = now()->endOfMonth()->toDateString();
            } else {
                $fechaInicio = $hoy;
                $fechaFin = $hoy;
            }
        }

        $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_gasto_id', $request->categoria_id);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('descripcion', 'like', "%{$buscar}%");
        }

        return [$query, $fechaInicio, $fechaFin];
    }
}
