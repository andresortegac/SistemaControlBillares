@extends('layouts.app')

@section('title', 'Reporte de Gastos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cash-stack"></i> Reporte de Gastos</h2>
    <button onclick="window.print()" class="btn btn-outline-primary">
        <i class="bi bi-printer"></i> Imprimir
    </button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reportes.gastos') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Anio</label>
                <input type="number" name="anio" class="form-control" min="2000" max="2100" value="{{ $anio }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Generar
                </button>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-wallet2"></i> Ir a Gastos
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-danger">${{ number_format($totales['gastos'], 2) }}</h4>
                <p class="mb-0 text-muted">Gastos del anio</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">${{ number_format($totales['ingresos'], 2) }}</h4>
                <p class="mb-0 text-muted">Ingresos del anio</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="{{ $totales['utilidad'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($totales['utilidad'], 2) }}</h4>
                <p class="mb-0 text-muted">Utilidad neta</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ingresos vs Gastos por Mes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Gastos</th>
                                <th class="text-end">Utilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparativaMensual as $item)
                            <tr>
                                <td>{{ ucfirst($item['nombre_mes']) }}</td>
                                <td class="text-end">${{ number_format($item['ingresos'], 2) }}</td>
                                <td class="text-end">${{ number_format($item['gastos'], 2) }}</td>
                                <td class="text-end">
                                    <strong class="{{ $item['utilidad'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($item['utilidad'], 2) }}
                                    </strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Categorias de Gasto</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCategorias as $categoria => $total)
                            <tr>
                                <td>{{ $categoria }}</td>
                                <td class="text-end"><strong>${{ number_format($total, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-3 text-muted">Sin datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
