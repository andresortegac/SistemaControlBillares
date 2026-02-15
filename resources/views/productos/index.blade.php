@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Productos</h2>
    <div>
        <a href="{{ route('productos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo Producto
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('productos.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o código..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="stock_bajo" id="stock_bajo" value="1" {{ request('stock_bajo') ? 'checked' : '' }}>
                    <label class="form-check-label" for="stock_bajo">
                        Solo stock bajo
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-end">Precio Venta</th>
                        <th class="text-center">Stock</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr class="{{ $producto->tieneStockBajo() ? 'table-warning' : '' }}">
                        <td><code>{{ $producto->codigo }}</code></td>
                        <td>
                            <strong>{{ $producto->nombre }}</strong>
                            @if($producto->descripcion)
                            <br><small class="text-muted">{{ Str::limit($producto->descripcion, 40) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $producto->categoria->nombre }}</span>
                        </td>
                        <td class="text-end">
                            <strong>${{ number_format($producto->precio_venta, 2) }}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $producto->tieneStockBajo() ? 'danger' : ($producto->stock == 0 ? 'secondary' : 'success') }}">
                                {{ $producto->stock }} {{ $producto->unidad_medida }}
                            </span>
                        </td>
                        <td>
                            @if($producto->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('productos.show', $producto) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-box" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No se encontraron productos</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($productos->hasPages())
    <div class="card-footer">
        {{ $productos->links() }}
    </div>
    @endif
</div>
@endsection
