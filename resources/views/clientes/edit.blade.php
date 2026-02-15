@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Editar Cliente - {{ $cliente->nombre }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $cliente->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                   id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}">
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tipo_membresia" class="form-label">Tipo de Membresía</label>
                            <select class="form-select @error('tipo_membresia') is-invalid @enderror" id="tipo_membresia" name="tipo_membresia">
                                <option value="ninguna" {{ old('tipo_membresia', $cliente->tipo_membresia) == 'ninguna' ? 'selected' : '' }}>Ninguna</option>
                                <option value="basica" {{ old('tipo_membresia', $cliente->tipo_membresia) == 'basica' ? 'selected' : '' }}>Básica (5% desc.)</option>
                                <option value="premium" {{ old('tipo_membresia', $cliente->tipo_membresia) == 'premium' ? 'selected' : '' }}>Premium (10% desc.)</option>
                                <option value="vip" {{ old('tipo_membresia', $cliente->tipo_membresia) == 'vip' ? 'selected' : '' }}>VIP (15% desc.)</option>
                            </select>
                            @error('tipo_membresia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="puntos_fidelidad" class="form-label">Puntos de Fidelidad</label>
                            <input type="number" class="form-control @error('puntos_fidelidad') is-invalid @enderror" 
                                   id="puntos_fidelidad" name="puntos_fidelidad" value="{{ old('puntos_fidelidad', $cliente->puntos_fidelidad) }}" min="0">
                            @error('puntos_fidelidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="saldo_favor" class="form-label">Saldo a Favor ($)</label>
                            <input type="number" step="0.01" class="form-control @error('saldo_favor') is-invalid @enderror" 
                                   id="saldo_favor" name="saldo_favor" value="{{ old('saldo_favor', $cliente->saldo_favor) }}" min="0">
                            @error('saldo_favor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                  id="direccion" name="direccion" rows="2">{{ old('direccion', $cliente->direccion) }}</textarea>
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="notas" class="form-label">Notas</label>
                        <textarea class="form-control @error('notas') is-invalid @enderror" 
                                  id="notas" name="notas" rows="2">{{ old('notas', $cliente->notas) }}</textarea>
                        @error('notas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $cliente->activo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">Activo</label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Actualizar
                        </button>
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
