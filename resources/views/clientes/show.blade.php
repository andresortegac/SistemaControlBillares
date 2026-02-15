@extends('layouts.app')

@section('title', 'Detalle de Cliente - ' . $cliente->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person"></i> {{ $cliente->nombre }}</h2>
    <div>
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Cliente -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    @if($cliente->telefono)
                    <tr>
                        <td><i class="bi bi-telephone"></i></td>
                        <td>{{ $cliente->telefono }}</td>
                    </tr>
                    @endif
                    @if($cliente->email)
                    <tr>
                        <td><i class="bi bi-envelope"></i></td>
                        <td>{{ $cliente->email }}</td>
                    </tr>
                    @endif
                    @if($cliente->fecha_nacimiento)
                    <tr>
                        <td><i class="bi bi-calendar"></i></td>
                        <td>{{ $cliente->fecha_nacimiento->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><i class="bi bi-star"></i></td>
                        <td>
                            <span class="badge bg-{{ $cliente->tipo_membresia == 'vip' ? 'danger' : ($cliente->tipo_membresia == 'premium' ? 'warning' : ($cliente->tipo_membresia == 'basica' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($cliente->tipo_membresia) }}
                            </span>
                            @if($cliente->descuento_membresia > 0)
                            <small class="text-success ms-2">-{{ $cliente->descuento_membresia }}% desc.</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-coin"></i></td>
                        <td><strong>{{ $cliente->puntos_fidelidad }}</strong> puntos</td>
                    </tr>
                    @if($cliente->saldo_favor > 0)
                    <tr>
                        <td><i class="bi bi-wallet2"></i></td>
                        <td class="text-success"><strong>${{ number_format($cliente->saldo_favor, 2) }}</strong> a favor</td>
                    </tr>
                    @endif
                </table>
                
                @if($cliente->direccion)
                <hr>
                <p class="text-muted"><i class="bi bi-geo-alt"></i> {{ $cliente->direccion }}</p>
                @endif
                
                @if($cliente->notas)
                <hr>
                <p class="text-muted small">{{ $cliente->notas }}</p>
                @endif
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4>{{ $cliente->total_visitas }}</h4>
                        <small class="text-muted">Visitas</small>
                    </div>
                    <div class="col-6">
                        <h4>${{ number_format($cliente->total_gastado, 0) }}</h4>
                        <small class="text-muted">Total Gastado</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Agregar Puntos -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Agregar Puntos</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('clientes.puntos', $cliente) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="number" name="puntos" class="form-control" placeholder="Cantidad" min="1" required>
                        <button type="submit" class="btn btn-primary">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Historial -->
    <div class="col-lg-8">
        <!-- Ventas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Últimas Ventas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Folio</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cliente->ventas->take(10) as $venta)
                            <tr>
                                <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-secondary">{{ $venta->folio }}</span></td>
                                <td class="text-end"><strong>${{ number_format($venta->total, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No hay ventas registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Uso de Mesas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> Historial de Mesas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Mesa</th>
                                <th>Minutos</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cliente->usosMesas->take(10) as $uso)
                            <tr>
                                <td>{{ $uso->hora_inicio->format('d/m/Y H:i') }}</td>
                                <td>{{ $uso->mesa->nombre }}</td>
                                <td>{{ $uso->minutos_totales ?? '-' }}</td>
                                <td class="text-end">
                                    @if($uso->total)
                                    <strong>${{ number_format($uso->total, 2) }}</strong>
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay uso de mesas registrado</td>
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
