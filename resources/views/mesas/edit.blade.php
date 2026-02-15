@extends('layouts.app')

@section('title', 'Editar Mesa')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Editar Mesa - {{ $mesa->nombre }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('mesas.update', $mesa) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre', $mesa->nombre) }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                            <option value="pool" {{ old('tipo', $mesa->tipo) == 'pool' ? 'selected' : '' }}>Pool</option>
                            <option value="snooker" {{ old('tipo', $mesa->tipo) == 'snooker' ? 'selected' : '' }}>Snooker</option>
                            <option value="carambola" {{ old('tipo', $mesa->tipo) == 'carambola' ? 'selected' : '' }}>Carambola</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                            <option value="disponible" {{ old('estado', $mesa->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="mantenimiento" {{ old('estado', $mesa->estado) == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                            <option value="reservada" {{ old('estado', $mesa->estado) == 'reservada' ? 'selected' : '' }}>Reservada</option>
                        </select>
                        @error('estado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nota: No se puede cambiar a "Ocupada" manualmente.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="precio_por_hora" class="form-label">Precio por Hora ($) *</label>
                        <input type="number" step="0.01" class="form-control @error('precio_por_hora') is-invalid @enderror" 
                               id="precio_por_hora" name="precio_por_hora" value="{{ old('precio_por_hora', $mesa->precio_por_hora) }}" required>
                        @error('precio_por_hora')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $mesa->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Actualizar
                        </button>
                        <a href="{{ route('mesas.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
