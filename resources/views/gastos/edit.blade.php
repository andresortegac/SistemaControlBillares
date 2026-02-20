@extends('layouts.app')

@section('title', 'Editar Gasto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Editar Gasto</h2>
    <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actualizar gasto #{{ $gasto->id }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('gastos.update', $gasto) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ old('fecha', $gasto->fecha->toDateString()) }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_gasto_id" class="form-select" required>
                                <option value="">Selecciona una categoria</option>
                                @foreach($categorias as $grupo => $items)
                                <optgroup label="{{ $grupo }}">
                                    @foreach($items as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_gasto_id', $gasto->categoria_gasto_id) == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" min="0.01" name="monto" class="form-control" value="{{ old('monto', $gasto->monto) }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Comprobante (opcional)</label>
                            <input type="file" name="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            @if($gasto->comprobante_path)
                            <small class="text-muted">Actual: <a href="{{ asset('storage/' . $gasto->comprobante_path) }}" target="_blank">ver comprobante</a></small>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripcion</label>
                            <textarea name="descripcion" rows="4" class="form-control" required>{{ old('descripcion', $gasto->descripcion) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
