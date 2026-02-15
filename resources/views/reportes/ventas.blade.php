@extends('layouts.app')

@section('title', 'Reporte de Ventas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-graph-up"></i> Reporte de Ventas</h2>
    <button onclick="window.print()" class="btn btn-outline-primary">
        <i class="bi bi-printer"></i> Imprimir
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reportes.ventas') }}" method="GET" class="row g-3">
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
                <h4 class="text-primary">${{ number_format($resumen['total_ventas'], 2) }}</h4>
                <p class="mb-0 text-muted">Total Ventas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $resumen['cantidad_ventas'] }}</h4>
                <p class="mb-0 text-muted">Cantidad de Ventas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">${{ number_format($resumen['promedio_venta'], 2) }}</h4>
                <p class="mb-0 text-muted">Promedio por Venta</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning">${{ number_format($resumen['total_descuentos'], 2) }}</h4>
                <p class="mb-0 text-muted">Total Descuentos</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Ventas por Día -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ventas por Día</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-center">Ventas</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasPorDia as $fecha => $datos)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $datos['cantidad'] }}</td>
                                <td class="text-end"><strong>${{ number_format($datos['total'], 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No hay datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ventas por Método de Pago -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Por Método de Pago</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasPorMetodo as $metodo => $datos)
                            <tr>
                                <td>{{ ucfirst($metodo) }}</td>
                                <td class="text-center">{{ $datos['cantidad'] }}</td>
                                <td class="text-end"><strong>${{ number_format($datos['total'], 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No hay datos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalle de Ventas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Detalle de Ventas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Desc.</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $venta->folio }}</span></td>
                        <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $venta->cliente ? $venta->cliente->nombre : 'General' }}</td>
                        <td>{{ $venta->usuario->name }}</td>
                        <td class="text-end">${{ number_format($venta->subtotal, 2) }}</td>
                        <td class="text-end">${{ number_format($venta->descuento, 2) }}</td>
                        <td class="text-end"><strong>${{ number_format($venta->total, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No hay ventas en este período</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
