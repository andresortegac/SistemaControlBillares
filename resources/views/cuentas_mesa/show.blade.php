@extends('layouts.app')

@section('title', 'Cuenta Activa - ' . $cuentaMesa->mesa->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-receipt-cutoff"></i> Cuenta de {{ $cuentaMesa->mesa->nombre }}</h2>
    <a href="{{ route('mesas.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Mesas
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted">Estado</div>
                <h5 class="mb-0">{{ strtoupper($cuentaMesa->estado) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted">Consumo Pendiente</div>
                <h5 class="mb-0">${{ number_format($consumoPendiente, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted">Saldo Jugadores</div>
                <h5 class="mb-0">${{ number_format($saldoTotal, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted">Jugadores Activos</div>
                <h5 class="mb-0">{{ $cuentaMesa->jugadores->where('activo', true)->count() }}</h5>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><strong>Agregar Jugador</strong></div>
            <div class="card-body">
                <form action="{{ route('cuentas_mesa.jugadores.store', $cuentaMesa) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <input type="text" class="form-control" name="nombre" placeholder="Nombre jugador" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Agregar</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Registrar Consumo General</strong></div>
            <div class="card-body">
                <form action="{{ route('cuentas_mesa.consumos.store', $cuentaMesa) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label mb-1">Producto</label>
                        <select class="form-select" name="producto_id" id="productoConsumo">
                            <option value="">Consumo manual (sin producto)</option>
                            @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" data-precio="{{ $producto->precio_venta }}">
                                {{ $producto->nombre }} | Stock: {{ $producto->stock }} | ${{ number_format($producto->precio_venta, 2) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label mb-1">Cantidad</label>
                        <input type="number" min="1" step="1" class="form-control" id="cantidadConsumo" name="cantidad" value="1">
                    </div>
                    <div class="mb-2">
                        <label class="form-label mb-1">Descripción</label>
                        <input type="text" class="form-control" name="descripcion" id="descripcionConsumo" placeholder="Opcional si eliges producto">
                    </div>
                    <div class="mb-2">
                        <label class="form-label mb-1">Monto</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" name="monto" id="montoConsumo" placeholder="Monto" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Agregar Consumo</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Cerrar Mesa</strong></div>
            <div class="card-body">
                <form action="{{ route('cuentas_mesa.cerrar', $cuentaMesa) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <textarea class="form-control" name="notas" rows="2" placeholder="Notas de cierre (opcional)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Cerrar Mesa</button>
                </form>
                <small class="text-muted d-block mt-2">Solo cierra si saldo total y consumo pendiente están en cero.</small>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Registrar Fin de Ronda</strong></div>
            <div class="card-body">
                <form action="{{ route('cuentas_mesa.rondas.store', $cuentaMesa) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Jugadores perdedores de la ronda</label>
                        <div class="row">
                            @forelse($cuentaMesa->jugadores->where('activo', true) as $jugador)
                            <div class="col-md-6">
                                <label class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="perdedores[]" value="{{ $jugador->id }}">
                                    <span class="form-check-label">{{ $jugador->nombre }}</span>
                                </label>
                            </div>
                            @empty
                            <div class="text-muted">No hay jugadores activos.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="mb-2">
                        <input type="text" class="form-control" name="detalle" placeholder="Detalle de ronda (opcional)">
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Aplicar Cargo a Perdedores</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Saldo por Jugador</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Jugador</th>
                            <th>Estado</th>
                            <th class="text-end">Saldo</th>
                            <th>Pago Parcial</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuentaMesa->jugadores as $jugador)
                        <tr>
                            <td>{{ $jugador->nombre }}</td>
                            <td>
                                <span class="badge bg-{{ $jugador->activo ? 'success' : 'secondary' }}">
                                    {{ $jugador->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end"><strong>${{ number_format($saldos[$jugador->id] ?? 0, 2) }}</strong></td>
                            <td>
                                <form action="{{ route('cuentas_mesa.pagos.store', [$cuentaMesa, $jugador]) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    <input type="number" name="monto" min="0.01" step="0.01" class="form-control form-control-sm" placeholder="Monto">
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Pagar</button>
                                </form>
                            </td>
                            <td>
                                @if($jugador->activo)
                                <form action="{{ route('cuentas_mesa.jugadores.inactivar', [$cuentaMesa, $jugador]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Inactivar</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No hay jugadores registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Historial de Movimientos</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Jugador</th>
                            <th>Descripción</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuentaMesa->movimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-dark">{{ $movimiento->tipo }}</span></td>
                            <td>{{ $movimiento->jugadorMesa?->nombre ?? 'General' }}</td>
                            <td>{{ $movimiento->descripcion }}</td>
                            <td class="text-end">${{ number_format($movimiento->monto, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Sin movimientos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const productoSelect = document.getElementById('productoConsumo');
        const cantidadInput = document.getElementById('cantidadConsumo');
        const montoInput = document.getElementById('montoConsumo');
        const descripcionInput = document.getElementById('descripcionConsumo');

        function actualizarMontoDesdeProducto() {
            const selected = productoSelect.selectedOptions[0];
            const productoId = productoSelect.value;
            const cantidad = Math.max(1, Number(cantidadInput.value || 1));
            const precio = Number(selected?.dataset?.precio || 0);

            if (productoId) {
                montoInput.value = (precio * cantidad).toFixed(2);
                montoInput.readOnly = true;
                montoInput.required = false;
                descripcionInput.placeholder = 'Opcional (si lo dejas vacío usa nombre del producto)';
            } else {
                montoInput.readOnly = false;
                montoInput.required = true;
                descripcionInput.placeholder = 'Ej: Gaseosas x 4';
            }
        }

        productoSelect.addEventListener('change', actualizarMontoDesdeProducto);
        cantidadInput.addEventListener('input', actualizarMontoDesdeProducto);
        actualizarMontoDesdeProducto();
    })();
</script>
@endpush
