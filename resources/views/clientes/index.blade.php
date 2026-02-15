@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Cliente
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('clientes.index') }}" method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre, teléfono o email..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-4">
                <select name="membresia" class="form-select">
                    <option value="">Todas las membresías</option>
                    <option value="ninguna" {{ request('membresia') == 'ninguna' ? 'selected' : '' }}>Ninguna</option>
                    <option value="basica" {{ request('membresia') == 'basica' ? 'selected' : '' }}>Básica</option>
                    <option value="premium" {{ request('membresia') == 'premium' ? 'selected' : '' }}>Premium</option>
                    <option value="vip" {{ request('membresia') == 'vip' ? 'selected' : '' }}>VIP</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Clientes -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Membresía</th>
                        <th class="text-center">Puntos</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                    <tr>
                        <td>
                            <strong>{{ $cliente->nombre }}</strong>
                            @if($cliente->fecha_nacimiento)
                            <br><small class="text-muted">Cumple: {{ $cliente->fecha_nacimiento->format('d/m') }}</small>
                            @endif
                        </td>
                        <td>
                            @if($cliente->telefono)
                            <div><i class="bi bi-telephone"></i> {{ $cliente->telefono }}</div>
                            @endif
                            @if($cliente->email)
                            <div><i class="bi bi-envelope"></i> {{ $cliente->email }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $cliente->tipo_membresia == 'vip' ? 'danger' : ($cliente->tipo_membresia == 'premium' ? 'warning' : ($cliente->tipo_membresia == 'basica' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($cliente->tipo_membresia) }}
                            </span>
                            @if($cliente->descuento_membresia > 0)
                            <br><small class="text-success">-{{ $cliente->descuento_membresia }}% desc.</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $cliente->puntos_fidelidad }} pts</span>
                        </td>
                        <td>
                            @if($cliente->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro?')">
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
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No se encontraron clientes</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($clientes->hasPages())
    <div class="card-footer">
        {{ $clientes->links() }}
    </div>
    @endif
</div>
@endsection
