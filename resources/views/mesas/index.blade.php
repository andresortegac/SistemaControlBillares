@extends('layouts.app')

@section('title', 'Gestión de Mesas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-grid-3x3"></i> Gestión de Mesas</h2>
    @if(auth()->user()->esAdmin() || auth()->user()->esGerente())
    <a href="{{ route('mesas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva Mesa
    </a>
    @endif
</div>

<!-- Resumen de Estados -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">{{ $mesas->where('estado', 'disponible')->count() }}</h3>
                <p class="mb-0 text-muted">Disponibles</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-danger">{{ $mesas->where('estado', 'ocupada')->count() }}</h3>
                <p class="mb-0 text-muted">Ocupadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning">{{ $mesas->where('estado', 'mantenimiento')->count() }}</h3>
                <p class="mb-0 text-muted">En Mantenimiento</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info">{{ $mesas->where('estado', 'reservada')->count() }}</h3>
                <p class="mb-0 text-muted">Reservadas</p>
            </div>
        </div>
    </div>
</div>

<!-- Grid de Mesas -->
<div class="row">
    @foreach($mesas as $mesa)
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card mesa-card h-100 @if($mesa->estado == 'disponible') mesa-disponible @elseif($mesa->estado == 'ocupada') mesa-ocupada @elseif($mesa->estado == 'mantenimiento') mesa-mantenimiento @else mesa-reservada @endif">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $mesa->nombre }}</h5>
                        <span class="badge bg-secondary">{{ ucfirst($mesa->tipo) }}</span>
                    </div>
                    <span class="badge-mesa @if($mesa->estado == 'disponible') mesa-disponible @elseif($mesa->estado == 'ocupada') mesa-ocupada @elseif($mesa->estado == 'mantenimiento') mesa-mantenimiento @else mesa-reservada @endif">
                        {{ ucfirst($mesa->estado) }}
                    </span>
                </div>
                
                <p class="mb-2">
                    <i class="bi bi-currency-dollar"></i> 
                    <strong>${{ number_format($mesa->precio_por_hora, 2) }}</strong> / hora
                </p>
                
                @if($mesa->descripcion)
                <p class="small text-muted mb-3">{{ Str::limit($mesa->descripcion, 50) }}</p>
                @endif
                
                @if($mesa->estaOcupada() && $mesa->usoActivo())
                    @php $uso = $mesa->usoActivo(); @endphp
                    <div class="bg-white bg-opacity-50 p-2 rounded mb-3">
                        <div class="d-flex justify-content-between">
                            <small><i class="bi bi-clock"></i> Tiempo:</small>
                            <strong class="timer" data-inicio="{{ $uso->hora_inicio }}">
                                {{ $uso->getTiempoTranscurrido()['formateado'] }}
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small><i class="bi bi-currency-dollar"></i> Total:</small>
                            <strong>${{ number_format($uso->getCostoActual(), 2) }}</strong>
                        </div>
                    </div>
                @endif
                
                <div class="d-flex gap-2 flex-wrap">
                    @if($mesa->estaDisponible())
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#iniciarModal{{ $mesa->id }}">
                            <i class="bi bi-play"></i> Iniciar
                        </button>
                    @elseif($mesa->estaOcupada())
                        <form action="{{ route('mesas.pausar', $mesa) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="bi bi-pause"></i>
                            </button>
                        </form>
                        <form action="{{ route('mesas.finalizar', $mesa) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-stop"></i> Cobrar
                            </button>
                        </form>
                    @endif
                    
                    <div class="dropdown d-inline">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('mesas.show', $mesa) }}"><i class="bi bi-eye"></i> Ver Detalles</a></li>
                            @if(auth()->user()->esAdmin() || auth()->user()->esGerente())
                            <li><a class="dropdown-item" href="{{ route('mesas.edit', $mesa) }}"><i class="bi bi-pencil"></i> Editar</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('mesas.destroy', $mesa) }}" method="POST" onsubmit="return confirm('¿Estás seguro?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i> Eliminar</button>
                                </form>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para iniciar uso -->
    @if($mesa->estaDisponible())
    <div class="modal fade" id="iniciarModal{{ $mesa->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Iniciar Uso - {{ $mesa->nombre }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('mesas.iniciar', $mesa) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente (opcional)</label>
                            <select name="cliente_id" class="form-select">
                                <option value="">Cliente General</option>
                                @foreach(\App\Models\Cliente::activos()->get() as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-play"></i> Iniciar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    function actualizarTimers() {
        document.querySelectorAll('.timer').forEach(timer => {
            const inicio = new Date(timer.dataset.inicio);
            const ahora = new Date();
            const diff = Math.floor((ahora - inicio) / 1000);
            
            const horas = Math.floor(diff / 3600);
            const minutos = Math.floor((diff % 3600) / 60);
            const segundos = diff % 60;
            
            timer.textContent = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
        });
    }
    
    setInterval(actualizarTimers, 1000);
</script>
@endpush
