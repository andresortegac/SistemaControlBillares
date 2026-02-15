@extends('layouts.app')

@section('title', 'Venta #' . $venta->folio)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt"></i> Venta #{{ $venta->folio }}</h2>
    <div>
        <a href="{{ route('ventas.ticket', $venta) }}" class="btn btn-info" target="_blank">
            <i class="bi bi-printer"></i> Ticket
        </a>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información de la Venta -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Vendedor:</strong></td>
                        <td>{{ $venta->usuario->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cliente:</strong></td>
                        <td>{{ $venta->cliente ? $venta->cliente->nombre : 'General' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td>
                            <span class="badge bg-{{ $venta->tipo == 'productos' ? 'info' : ($venta->tipo == 'mesa' ? 'warning' : 'primary') }}">
                                {{ ucfirst($venta->tipo) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Método de Pago:</strong></td>
                        <td>{{ ucfirst($venta->metodo_pago) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            <span class="badge bg-{{ $venta->estado == 'pagada' ? 'success' : ($venta->estado == 'pendiente' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        </td>
                    </tr>
                    @if($venta->efectivo_recibido)
                    <tr>
                        <td><strong>Efectivo:</strong></td>
                        <td>${{ number_format($venta->efectivo_recibido, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cambio:</strong></td>
                        <td>${{ number_format($venta->cambio, 2) }}</td>
                    </tr>
                    @endif
                </table>
                
                @if($venta->notas)
                <hr>
                <p class="text-muted">{{ $venta->notas }}</p>
                @endif
            </div>
        </div>
        
        <!-- Totales -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Totales</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>${{ number_format($venta->subtotal, 2) }}</span>
                </div>
                @if($venta->descuento > 0)
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Descuento:</span>
                    <span>-${{ number_format($venta->descuento, 2) }}</span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between">
                    <h5 class="mb-0">Total:</h5>
                    <h5 class="mb-0 text-primary">${{ number_format($venta->total, 2) }}</h5>
                </div>
            </div>
        </div>
        
        @if($venta->estado != 'cancelada')
        <form action="{{ route('ventas.destroy', $venta) }}" method="POST" class="mt-3" onsubmit="return confirm('¿Estás seguro de cancelar esta venta?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger w-100">
                <i class="bi bi-x-lg"></i> Cancelar Venta
            </button>
        </form>
        @endif
    </div>
    
    <!-- Detalles -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list"></i> Detalles</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($venta->detalles as $detalle)
                            <tr>
                                <td>
                                    @if($detalle->producto)
                                        {{ $detalle->producto->nombre }}
                                    @else
                                        <em>{{ $detalle->descripcion }}</em>
                                    @endif
                                </td>
                                <td class="text-center">{{ $detalle->cantidad }}</td>
                                <td class="text-end">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="text-end"><strong>${{ number_format($detalle->subtotal, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay detalles</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>${{ number_format($venta->subtotal, 2) }}</strong></td>
                            </tr>
                            @if($venta->descuento > 0)
                            <tr>
                                <td colspan="3" class="text-end text-success"><strong>Descuento:</strong></td>
                                <td class="text-end text-success"><strong>-${{ number_format($venta->descuento, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><h5 class="mb-0">Total:</h5></td>
                                <td class="text-end"><h5 class="mb-0">${{ number_format($venta->total, 2) }}</h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
