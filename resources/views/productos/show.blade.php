@extends('layouts.app')

@section('title', 'Detalle de Producto - ' . $producto->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> {{ $producto->nombre }}</h2>
    <div>
        <a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Producto -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Código:</strong></td>
                        <td><code>{{ $producto->codigo }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Categoría:</strong></td>
                        <td><span class="badge bg-info">{{ $producto->categoria->nombre }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Precio Compra:</strong></td>
                        <td>${{ number_format($producto->precio_compra, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Precio Venta:</strong></td>
                        <td><strong>${{ number_format($producto->precio_venta, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Margen:</strong></td>
                        <td>
                            <span class="badge bg-{{ $producto->margen_ganancia > 30 ? 'success' : ($producto->margen_ganancia > 15 ? 'warning' : 'danger') }}">
                                {{ number_format($producto->margen_ganancia, 1) }}%
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Stock:</strong></td>
                        <td>
                            <span class="badge bg-{{ $producto->tieneStockBajo() ? 'danger' : 'success' }}">
                                {{ $producto->stock }} {{ $producto->unidad_medida }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Stock Mínimo:</strong></td>
                        <td>{{ $producto->stock_minimo }} {{ $producto->unidad_medida }}</td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @if($producto->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                </table>
                
                @if($producto->descripcion)
                <hr>
                <p class="text-muted">{{ $producto->descripcion }}</p>
                @endif
            </div>
        </div>
        
        <!-- Ajustar Stock -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-down-up"></i> Ajustar Stock</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('productos.stock', $producto) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <textarea name="motivo" class="form-control" rows="2" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Ajustar Stock</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Historial de Ventas -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Ventas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Folio</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($producto->detalleVentas->take(20) as $detalle)
                            <tr>
                                <td>{{ $detalle->venta->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-secondary">{{ $detalle->venta->folio }}</span></td>
                                <td class="text-center">{{ $detalle->cantidad }}</td>
                                <td class="text-end">${{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="text-end"><strong>${{ number_format($detalle->subtotal, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No hay ventas registradas
                                </td>
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
