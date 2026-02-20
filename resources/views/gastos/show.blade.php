@extends('layouts.app')

@section('title', 'Detalle de Gasto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt"></i> Gasto #{{ $gasto->id }}</h2>
    <div class="d-flex gap-2">
        @if(auth()->user()->esAdmin())
        <a href="{{ route('gastos.edit', $gasto) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        @endif
        <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informacion del gasto</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Categoria:</strong></td>
                        <td>
                            <span class="badge bg-secondary">{{ $gasto->categoria->nombre }}</span>
                            <small class="text-muted ms-2">{{ $gasto->categoria->grupo }}</small>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Monto:</strong></td>
                        <td><h4 class="text-danger mb-0">${{ number_format($gasto->monto, 2) }}</h4></td>
                    </tr>
                    <tr>
                        <td><strong>Registrado por:</strong></td>
                        <td>{{ $gasto->usuario->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Descripcion:</strong></td>
                        <td>{{ $gasto->descripcion }}</td>
                    </tr>
                    <tr>
                        <td><strong>Comprobante:</strong></td>
                        <td>
                            @if($gasto->comprobante_path)
                            <a href="{{ asset('storage/' . $gasto->comprobante_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-paperclip"></i> Ver comprobante
                            </a>
                            @else
                            <span class="text-muted">No adjunto</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
