@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cart3"></i> Ventas</h2>
    <a href="{{ route('ventas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva Venta
    </a>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Ventas Hoy</h6>
                        <h3 class="mb-0">${{ number_format($totalVentas, 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar" style="font-size: 2.5rem; color: var(--success-color);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Cantidad de Ventas Hoy</h6>
                        <h3 class="mb-0">{{ $cantidadVentas }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-receipt" style="font-size: 2.5rem; color: #17a2b8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('ventas.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="date" name="fecha" class="form-control" value="{{ request('fecha') }}">
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="productos" {{ request('tipo') == 'productos' ? 'selected' : '' }}>Productos</option>
                    <option value="mesa" {{ request('tipo') == 'mesa' ? 'selected' : '' }}>Mesa</option>
                    <option value="mixta" {{ request('tipo') == 'mixta' ? 'selected' : '' }}>Mixta</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Ventas -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th class="text-end">Total</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr class="{{ $venta->estado == 'cancelada' ? 'table-secondary' : '' }}">
                        <td><span class="badge bg-dark">{{ $venta->folio }}</span></td>
                        <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $venta->cliente ? $venta->cliente->nombre : 'General' }}</td>
                        <td>
                            <span class="badge bg-{{ $venta->tipo == 'productos' ? 'info' : ($venta->tipo == 'mesa' ? 'warning' : 'primary') }}">
                                {{ ucfirst($venta->tipo) }}
                            </span>
                        </td>
                        <td class="text-end"><strong>${{ number_format($venta->total, 2) }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $venta->metodo_pago == 'efectivo' ? 'success' : ($venta->metodo_pago == 'tarjeta' ? 'info' : 'warning') }}">
                                {{ ucfirst($venta->metodo_pago) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $venta->estado == 'pagada' ? 'success' : ($venta->estado == 'pendiente' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('ventas.ticket', $venta) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                            @if($venta->estado != 'cancelada')
                            <form action="{{ route('ventas.destroy', $venta) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta venta?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No se encontraron ventas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ventas->hasPages())
    <div class="card-footer">
        {{ $ventas->links() }}
    </div>
    @endif
</div>
@endsection
