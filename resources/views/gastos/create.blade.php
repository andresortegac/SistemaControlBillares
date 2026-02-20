@extends('layouts.app')

@section('title', 'Nuevo Gasto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Nuevo Gasto</h2>
    <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Datos del gasto</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('gastos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_gasto_id" class="form-select" required>
                                <option value="">Selecciona una categoria</option>
                                @foreach($categorias as $grupo => $items)
                                <optgroup label="{{ $grupo }}">
                                    @foreach($items as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_gasto_id') == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" min="0.01" name="monto" class="form-control" value="{{ old('monto') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Comprobante (opcional)</label>
                            <input type="file" name="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Formatos permitidos: JPG, PNG, PDF (max 4MB)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripcion</label>
                            <textarea name="descripcion" rows="4" class="form-control" required>{{ old('descripcion') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Gasto
                        </button>
                        <a href="{{ route('gastos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
