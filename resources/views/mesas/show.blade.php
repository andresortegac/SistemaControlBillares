@extends('layouts.app')

@section('title', 'Detalle de Mesa - ' . $mesa->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-grid-3x3"></i> {{ $mesa->nombre }}</h2>
    <div>
        <a href="{{ route('mesas.edit', $mesa) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('mesas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información de la Mesa -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td><span class="badge bg-info">{{ ucfirst($mesa->tipo) }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            <span class="badge bg-{{ $mesa->estado == 'disponible' ? 'success' : ($mesa->estado == 'ocupada' ? 'danger' : ($mesa->estado == 'mantenimiento' ? 'warning' : 'info')) }}">
                                {{ ucfirst($mesa->estado) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Precio/Hora:</strong></td>
                        <td>${{ number_format($mesa->precio_por_hora, 2) }}</td>
                    </tr>
                    @if($mesa->descripcion)
                    <tr>
                        <td><strong>Descripción:</strong></td>
                        <td>{{ $mesa->descripcion }}</td>
                    </tr>
                    @endif
                </table>
                
                @if($mesa->estaDisponible())
                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#iniciarModal">
                    <i class="bi bi-play"></i> Iniciar Uso
                </button>
                @elseif($mesa->estaOcupada() && $mesa->usoActivo())
                <div class="d-flex gap-2">
                    <form action="{{ route('mesas.pausar', $mesa) }}" method="POST" class="flex-fill">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-pause"></i> Pausar
                        </button>
                    </form>
                    <form action="{{ route('mesas.finalizar', $mesa) }}" method="POST" class="flex-fill">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-stop"></i> Finalizar
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        
        @if($mesa->estaOcupada() && $mesa->usoActivo())
        @php $uso = $mesa->usoActivo(); @endphp
        <div class="card mt-3 bg-danger text-white">
            <div class="card-body text-center">
                <h6>Tiempo Transcurrido</h6>
                <h2 class="timer mb-0" data-inicio="{{ $uso->hora_inicio }}">
                    {{ $uso->getTiempoTranscurrido()['formateado'] }}
                </h2>
                <hr class="my-3 border-white">
                <h6>Costo Actual</h6>
                <h3 class="mb-0">${{ number_format($uso->getCostoActual(), 2) }}</h3>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Historial de Uso -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Uso</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Cliente</th>
                                <th>Minutos</th>
                                <th class="text-end">Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mesa->usos as $uso)
                            <tr>
                                <td>{{ $uso->hora_inicio->format('d/m/Y H:i') }}</td>
                                <td>{{ $uso->hora_fin ? $uso->hora_fin->format('d/m/Y H:i') : '-' }}</td>
                                <td>{{ $uso->cliente ? $uso->cliente->nombre : 'General' }}</td>
                                <td>{{ $uso->minutos_totales ?? '-' }}</td>
                                <td class="text-end">
                                    @if($uso->total)
                                    <strong>${{ number_format($uso->total, 2) }}</strong>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $uso->estado == 'finalizada' ? 'success' : ($uso->estado == 'en_curso' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($uso->estado) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay historial de uso
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para iniciar uso -->
@if($mesa->estaDisponible())
<div class="modal fade" id="iniciarModal" tabindex="-1">
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
