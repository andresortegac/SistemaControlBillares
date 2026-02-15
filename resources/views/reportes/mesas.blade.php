@extends('layouts.app')

@section('title', 'Reporte de Uso de Mesas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-table"></i> Reporte de Uso de Mesas</h2>
    <button onclick="window.print()" class="btn btn-outline-primary">
        <i class="bi bi-printer"></i> Imprimir
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reportes.mesas') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Generar Reporte
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ $resumen['total_usos'] }}</h4>
                <p class="mb-0 text-muted">Total Usos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ floor($resumen['minutos_totales'] / 60) }}h {{ $resumen['minutos_totales'] % 60 }}m</h4>
                <p class="mb-0 text-muted">Tiempo Total</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">${{ number_format($resumen['ingresos_totales'], 2) }}</h4>
                <p class="mb-0 text-muted">Ingresos Totales</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning">{{ floor($resumen['promedio_minutos'] / 60) }}h {{ floor($resumen['promedio_minutos'] % 60) }}m</h4>
                <p class="mb-0 text-muted">Promedio por Uso</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Uso por Mesa -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Uso por Mesa</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mesa</th>
                                <th class="text-center">Usos</th>
                                <th class="text-center">Minutos</th>
                                <th class="text-end">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usoPorMesa as $datos)
                            <tr>
                                <td>{{ $datos['mesa'] }}</td>
                                <td class="text-center">{{ $datos['usos'] }}</td>
                                <td class="text-center">{{ floor($datos['minutos'] / 60) }}h</td>
                                <td class="text-end"><strong>${{ number_format($datos['ingresos'], 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detalle de Usos -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detalle de Usos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mesa</th>
                                <th>Cliente</th>
                                <th>Minutos</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usos as $uso)
                            <tr>
                                <td>{{ $uso->mesa->nombre }}</td>
                                <td>{{ $uso->cliente ? $uso->cliente->nombre : 'General' }}</td>
                                <td>{{ $uso->minutos_totales }}</td>
                                <td class="text-end"><strong>${{ number_format($uso->total, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay datos en este período</td>
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
