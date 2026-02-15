@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tags"></i> Categorías</h2>
    <a href="{{ route('categorias.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva Categoría
    </a>
</div>

<div class="row">
    @foreach($categorias as $categoria)
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-{{ $categoria->tipo == 'bebida' ? 'info' : ($categoria->tipo == 'alimento' ? 'warning' : ($categoria->tipo == 'accesorio' ? 'success' : 'secondary')) }}">
                        {{ ucfirst($categoria->tipo) }}
                    </span>
                    @if($categoria->activo)
                        <span class="badge bg-success">Activa</span>
                    @else
                        <span class="badge bg-secondary">Inactiva</span>
                    @endif
                </div>
                
                <h5 class="card-title">{{ $categoria->nombre }}</h5>
                
                @if($categoria->descripcion)
                <p class="card-text text-muted small">{{ Str::limit($categoria->descripcion, 60) }}</p>
                @endif
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted">
                        <i class="bi bi-box"></i> {{ $categoria->productos_count }} productos
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('categorias.edit', $categoria) }}"><i class="bi bi-pencil"></i> Editar</a></li>
                            <li>
                                <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" onsubmit="return confirm('¿Estás seguro?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i> Eliminar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
