@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
    <a href="{{ route('ventas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva Venta
    </a>
</div>

<!-- Estadísticas del Día -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Ventas Hoy</h6>
                        <h3 class="mb-0">{{ $ventasHoy }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-check" style="font-size: 2.5rem; color: var(--success-color);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Ingresos Hoy</h6>
                        <h3 class="mb-0">${{ number_format($ingresosHoy, 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar" style="font-size: 2.5rem; color: var(--highlight-color);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Mesas Ocupadas</h6>
                        <h3 class="mb-0">{{ $mesasOcupadas }} / {{ $totalMesas }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-grid-3x3" style="font-size: 2.5rem; color: #17a2b8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Clientes</h6>
                        <h3 class="mb-0">{{ $totalClientes }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people" style="font-size: 2.5rem; color: var(--warning-color);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Mesas en Uso -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> Mesas en Uso</h5>
                <a href="{{ route('mesas.index') }}" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body">
                @if($mesasEnUso->count() > 0)
                    <div class="row">
                        @foreach($mesasEnUso as $uso)
                        <div class="col-md-6 mb-3">
                            <div class="card mesa-card mesa-ocupada">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">{{ $uso->mesa->nombre }}</h5>
                                            <p class="mb-1 small">
                                                <i class="bi bi-person"></i> 
                                                {{ $uso->cliente ? $uso->cliente->nombre : 'Cliente General' }}
                                            </p>
                                            <p class="mb-0 small text-muted">
                                                <i class="bi bi-clock"></i> 
                                                Inicio: {{ $uso->hora_inicio->format('H:i') }}
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <div class="timer" data-inicio="{{ $uso->hora_inicio }}">
                                                {{ $uso->getTiempoTranscurrido()['formateado'] }}
                                            </div>
                                            <h5 class="mb-0 mt-1">${{ number_format($uso->getCostoActual(), 2) }}</h5>
                                        </div>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <form action="{{ route('mesas.pausar', $uso->mesa) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pause"></i> Pausar
                                            </button>
                                        </form>
                                        <form action="{{ route('mesas.finalizar', $uso->mesa) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-stop"></i> Finalizar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-grid-3x3" style="font-size: 3rem;"></i>
                        <p class="mt-3">No hay mesas en uso actualmente</p>
                        <a href="{{ route('mesas.index') }}" class="btn btn-primary">
                            <i class="bi bi-play"></i> Iniciar Mesa
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Productos con Stock Bajo -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Stock Bajo</h5>
            </div>
            <div class="card-body p-0">
                @if($productosStockBajo->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($productosStockBajo as $producto)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $producto->nombre }}</h6>
                                <small class="text-muted">{{ $producto->categoria->nombre }}</small>
                            </div>
                            <span class="badge bg-danger">{{ $producto->stock }} / {{ $producto->stock_minimo }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle" style="font-size: 2rem; color: var(--success-color);"></i>
                        <p class="mt-2 mb-0">Todo el inventario está bien</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Ventas Recientes -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Ventas Recientes</h5>
                <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th>Hora</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasRecientes as $venta)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $venta->folio }}</span></td>
                                <td>{{ $venta->cliente ? $venta->cliente->nombre : 'General' }}</td>
                                <td><strong>${{ number_format($venta->total, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $venta->metodo_pago === 'efectivo' ? 'success' : ($venta->metodo_pago === 'tarjeta' ? 'info' : 'warning') }}">
                                        {{ ucfirst($venta->metodo_pago) }}
                                    </span>
                                </td>
                                <td>{{ $venta->created_at->format('H:i') }}</td>
                                <td>
                                    <a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay ventas recientes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Productos Más Vendidos -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Más Vendidos Hoy</h5>
            </div>
            <div class="card-body p-0">
                @if($productosTop->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($productosTop as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $item->producto->nombre }}</h6>
                            </div>
                            <span class="badge bg-primary">{{ $item->cantidad_vendida }} vendidos</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <p class="mb-0">Sin ventas hoy</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Actualizar timers en tiempo real
    function actualizarTimers() {
        document.querySelectorAll('.timer').forEach(timer => {
            const inicio = new Date(timer.dataset.inicio);
            const ahora = new Date();
            const diff = Math.floor((ahora - inicio) / 1000);
            
            const horas = Math.floor(diff / 3600);
            const minutos = Math.floor((diff % 3600) / 60);
            const segundos = diff % 60;
            
            timer.textContent = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
        });
    }
    
    setInterval(actualizarTimers, 1000);
</script>
@endpush
