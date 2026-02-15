@extends('layouts.app')

@section('title', 'Editar Categoría')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Editar Categoría - {{ $categoria->nombre }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('categorias.update', $categoria) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre) }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                            <option value="bebida" {{ old('tipo', $categoria->tipo) == 'bebida' ? 'selected' : '' }}>Bebida</option>
                            <option value="alimento" {{ old('tipo', $categoria->tipo) == 'alimento' ? 'selected' : '' }}>Alimento</option>
                            <option value="accesorio" {{ old('tipo', $categoria->tipo) == 'accesorio' ? 'selected' : '' }}>Accesorio</option>
                            <option value="otro" {{ old('tipo', $categoria->tipo) == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $categoria->activo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">Activa</label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Actualizar
                        </button>
                        <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
