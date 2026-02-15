@extends('layouts.app')

@section('title', 'Reporte de Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Reporte de Clientes</h2>
    <button onclick="window.print()" class="btn btn-outline-primary">
        <i class="bi bi-printer"></i> Imprimir
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reportes.clientes') }}" method="GET" class="row g-3">
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
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ $nuevosClientes }}</h4>
                <p class="mb-0 text-muted">Nuevos Clientes</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $clientesFrecuentes->count() }}</h4>
                <p class="mb-0 text-muted">Clientes Activos</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">${{ number_format($clientesFrecuentes->sum('ventas_sum_total'), 0) }}</h4>
                <p class="mb-0 text-muted">Total Ventas</p>
            </div>
        </div>
    </div>
</div>

<!-- Clientes Frecuentes -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-trophy"></i> Clientes Más Frecuentes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Membresía</th>
                        <th class="text-center">Visitas</th>
                        <th class="text-end">Total Gastado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientesFrecuentes as $index => $cliente)
                    <tr>
                        <td><span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">{{ $index + 1 }}</span></td>
                        <td><strong>{{ $cliente->nombre }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $cliente->tipo_membresia == 'vip' ? 'danger' : ($cliente->tipo_membresia == 'premium' ? 'warning' : ($cliente->tipo_membresia == 'basica' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($cliente->tipo_membresia) }}
                            </span>
                        </td>
                        <td class="text-center"><strong>{{ $cliente->usos_mesas_count }}</strong></td>
                        <td class="text-end"><strong>${{ number_format($cliente->ventas_sum_total ?? 0, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No hay datos en este período</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
