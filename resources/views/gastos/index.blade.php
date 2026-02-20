@extends('layouts.app')

@section('title', 'Gastos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-wallet2"></i> Gastos</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('gastos.export.csv', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel (CSV)
        </a>
        @if(auth()->user()->esAdmin())
        <a href="{{ route('gastos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo Gasto
        </a>
        @endif
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-danger">${{ number_format($resumen['total_gastos'], 2) }}</h4>
                <p class="mb-0 text-muted">Total Gastos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $resumen['cantidad_gastos'] }}</h4>
                <p class="mb-0 text-muted">Registros</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">${{ number_format($resumen['ingresos'], 2) }}</h4>
                <p class="mb-0 text-muted">Ingresos del periodo</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="{{ $resumen['utilidad'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($resumen['utilidad'], 2) }}</h4>
                <p class="mb-0 text-muted">Utilidad del periodo</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('gastos.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Periodo</label>
                <select name="periodo" class="form-select">
                    <option value="diario" {{ request('periodo', 'diario') == 'diario' ? 'selected' : '' }}>Diario</option>
                    <option value="mensual" {{ request('periodo') == 'mensual' ? 'selected' : '' }}>Mensual</option>
                    <option value="anual" {{ request('periodo') == 'anual' ? 'selected' : '' }}>Anual</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio', $fechaInicio) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin', $fechaFin) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoria</label>
                <select name="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->grupo }} - {{ $categoria->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar descripcion</label>
                <input type="text" name="buscar" class="form-control" placeholder="Ej: mantenimiento mesa 4" value="{{ request('buscar') }}">
            </div>
            <div class="col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('reportes.gastos') }}" class="btn btn-outline-dark">
                    <i class="bi bi-graph-up"></i> Ver Reporte Mensual
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Categoria</th>
                        <th>Descripcion</th>
                        <th class="text-end">Monto</th>
                        <th>Registrado por</th>
                        <th class="text-center">Comprobante</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gastos as $gasto)
                    <tr>
                        <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $gasto->categoria->nombre }}</span>
                            <br><small class="text-muted">{{ $gasto->categoria->grupo }}</small>
                        </td>
                        <td>{{ Str::limit($gasto->descripcion, 70) }}</td>
                        <td class="text-end"><strong>${{ number_format($gasto->monto, 2) }}</strong></td>
                        <td>{{ $gasto->usuario->name }}</td>
                        <td class="text-center">
                            @if($gasto->comprobante_path)
                            <a href="{{ asset('storage/' . $gasto->comprobante_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-paperclip"></i>
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('gastos.show', $gasto) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->esAdmin())
                            <a href="{{ route('gastos.edit', $gasto) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este gasto? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-wallet2" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No hay gastos registrados para estos filtros</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($gastos->hasPages())
    <div class="card-footer">
        {{ $gastos->links() }}
    </div>
    @endif
</div>
@endsection
