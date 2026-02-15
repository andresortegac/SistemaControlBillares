@extends('layouts.app')

@section('title', 'Reporte de Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box"></i> Reporte de Productos</h2>
    <button onclick="window.print()" class="btn btn-outline-primary">
        <i class="bi bi-printer"></i> Imprimir
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('reportes.productos') }}" method="GET" class="row g-3">
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

<!-- Productos Más Vendidos -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-trophy"></i> Productos Más Vendidos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th class="text-center">Cantidad Vendida</th>
                        <th class="text-end">Total Ventas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productosMasVendidos as $index => $producto)
                    <tr>
                        <td><span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">{{ $index + 1 }}</span></td>
                        <td><code>{{ $producto->codigo }}</code></td>
                        <td>{{ $producto->nombre }}</td>
                        <td class="text-center"><strong>{{ $producto->cantidad_vendida }}</strong></td>
                        <td class="text-end">${{ number_format($producto->total_ventas, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No hay datos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Productos con Stock Bajo -->
<div class="card">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Productos con Stock Bajo</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-center">Stock Actual</th>
                        <th class="text-center">Stock Mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productosStockBajo as $producto)
                    <tr class="table-warning">
                        <td><code>{{ $producto->codigo }}</code></td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->categoria->nombre }}</td>
                        <td class="text-center"><span class="badge bg-danger">{{ $producto->stock }}</span></td>
                        <td class="text-center">{{ $producto->stock_minimo }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle text-success"></i> No hay productos con stock bajo
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
