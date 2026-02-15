@extends('layouts.app')

@section('title', 'Nueva Mesa')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-lg"></i> Nueva Mesa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('mesas.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                            <option value="pool" {{ old('tipo') == 'pool' ? 'selected' : '' }}>Pool</option>
                            <option value="snooker" {{ old('tipo') == 'snooker' ? 'selected' : '' }}>Snooker</option>
                            <option value="carambola" {{ old('tipo') == 'carambola' ? 'selected' : '' }}>Carambola</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="precio_por_hora" class="form-label">Precio por Hora ($) *</label>
                        <input type="number" step="0.01" class="form-control @error('precio_por_hora') is-invalid @enderror" 
                               id="precio_por_hora" name="precio_por_hora" value="{{ old('precio_por_hora', 50) }}" required>
                        @error('precio_por_hora')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Guardar
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
