@extends('layouts.app')

@section('title', 'Partida - ' . $mesa->nombre)

@push('styles')
<style>
    .producto-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .producto-item:hover {
        background-color: #f8f9fa;
    }
    .consumo-badge {
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-grid-3x3"></i> {{ $mesa->nombre }} - Control de Partida</h2>
    <a href="{{ route('mesas.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Jugadores y Equipos (2 vs 2)</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @for($i = 0; $i < 4; $i++)
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <label class="form-label mb-1">Jugador {{ $i + 1 }}</label>
                            <input type="text" class="form-control mb-2 jugador-nombre" data-index="{{ $i }}" placeholder="Nombre" value="{{ $i === 0 ? auth()->user()->name : '' }}">
                            <select class="form-select jugador-equipo" data-index="{{ $i }}">
                                <option value="1" {{ $i < 2 ? 'selected' : '' }}>Equipo 1</option>
                                <option value="2" {{ $i >= 2 ? 'selected' : '' }}>Equipo 2</option>
                            </select>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Tabla de Participantes</h5>
                <span class="text-muted small">Ronda repartida al equipo perdedor</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Jugador</th>
                                <th>Equipo</th>
                                <th class="text-end">Consumo Productos</th>
                                <th class="text-end">Ronda</th>
                                <th class="text-end">Total a Pagar</th>
                            </tr>
                        </thead>
                        <tbody id="tablaJugadores"></tbody>
                        <tfoot id="tablaJugadoresFooter"></tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cart3"></i> Carrito por Mesa</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Jugador</th>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tablaConsumos">
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Sin consumos registrados</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check2-square"></i> Resultado de la Ronda</h5>
            </div>
            <div class="card-body">
                <form id="formCierre" action="{{ route('mesas.cerrar_partida', $mesa) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Valor de la Ronda</label>
                            <input type="text" inputmode="decimal" class="form-control" id="valorRonda" value="" placeholder="Ej: 20000 o 20.000">
                            <small class="text-muted d-block mt-1">Subtotal carrito: <strong id="subtotalCarritoRonda">$0.00</strong></small>
                            <small class="text-muted d-block">Valor aplicado en esta ronda: <strong id="valorAplicadoRonda">$0.00</strong></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Equipo Perdedor</label>
                            <select class="form-select" id="perdedorEquipo">
                                <option value="1">Equipo 1</option>
                                <option value="2">Equipo 2</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-secondary mb-0 py-2 h-100 d-flex align-items-center">
                                Se sumarÃ¡ automÃ¡ticamente a los 2 jugadores del equipo perdedor.
                            </div>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <div class="alert alert-info mb-0 w-100 py-2">
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal Equipo 1: <strong id="subtotalEq1">$0.00</strong></span>
                                    <span>Subtotal Equipo 2: <strong id="subtotalEq2">$0.00</strong></span>
                                    <span>Total Mesa: <strong id="totalMesa">$0.00</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <div class="small text-danger mb-2" id="errorEquipos"></div>
                        <div class="small text-success mb-2" id="okRonda"></div>
                        <span class="badge bg-dark me-2" id="contadorRondas">Rondas: 0</span>
                        <button type="button" class="btn btn-success btn-lg" id="btnAgregarPerdedor" onclick="agregarPerdedor()">
                            <i class="bi bi-check-lg"></i> Agregar Perdedor (Ronda 1)
                        </button>
                    </div>

                    <div id="hiddenInputs"></div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Rondas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Equipo Perdedor</th>
                                <th class="text-end">Valor Ronda</th>
                                <th class="text-end">Valor por Jugador</th>
                            </tr>
                        </thead>
                        <tbody id="tablaRondas">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-2">Sin rondas cerradas</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bag"></i> Productos para Agregar</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Asignar consumo</label>
                    <select class="form-select" id="jugadorConsumo"></select>
                    <small class="text-muted" id="ayudaConsumoJugador">Por defecto se asigna a la cuenta del usuario en sesión (separada de los jugadores).</small>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
                </div>
                <div id="listaProductos" style="max-height: 420px; overflow-y: auto;">
                    @foreach($productos as $producto)
                    <div class="producto-item border rounded p-2 mb-2"
                        data-id="{{ $producto->id }}"
                        data-nombre="{{ $producto->nombre }}"
                        data-precio="{{ $producto->precio_venta }}"
                        data-stock="{{ $producto->stock }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $producto->nombre }}</strong>
                                <div class="small text-muted">
                                    Stock: <span class="stock-view">{{ $producto->stock }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div><strong>${{ number_format($producto->precio_venta, 2) }}</strong></div>
                                <button type="button" class="btn btn-sm btn-primary mt-1 btn-add-producto">
                                    <i class="bi bi-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $productosJson = $productos->map(fn ($producto) => [
        'id' => $producto->id,
        'nombre' => $producto->nombre,
        'precio' => (float) $producto->precio_venta,
        'stock' => (int) $producto->stock,
    ])->values()->all();
@endphp
<script>
    const productos = @json($productosJson);
    const usuarioSesion = @json(auth()->user()->name);
    const cuentaSesionKey = 'sesion';
    const cuentaSesionLabel = `${usuarioSesion} (Cuenta aparte)`;

    const jugadores = [
        { nombre: '', equipo: 1 },
        { nombre: '', equipo: 1 },
        { nombre: '', equipo: 2 },
        { nombre: '', equipo: 2 },
    ];

    let consumos = [];
    let rondasCerradas = 0;
    let rondaAcumuladaPorJugador = [0, 0, 0, 0];
    let historialRondas = [];
    let subtotalCarritoProcesado = 0;
    const formatoMiles = new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

    function formatoMoneda(valor) {
        return `$${formatoMiles.format(Math.round(Number(valor) || 0))}`;
    }

    function parseValorRonda(inputValue) {
        const raw = String(inputValue ?? '').trim();
        if (!raw) return 0;
        const cleaned = raw.replace(/[^\d.,]/g, '');

        if (cleaned.includes(',') && cleaned.includes('.')) {
            return Number(cleaned.replace(/\./g, '').replace(',', '.')) || 0;
        }

        if (cleaned.includes(',')) {
            return Number(cleaned.replace(/,/g, '')) || 0;
        }

        if (cleaned.includes('.')) {
            const parts = cleaned.split('.');
            if (parts.length > 1 && parts[parts.length - 1].length === 3) {
                return Number(cleaned.replace(/\./g, '')) || 0;
            }
        }

        return Number(cleaned) || 0;
    }

    function normalizarValorRonda(inputValue) {
        const valor = parseValorRonda(inputValue);
        return valor > 0 ? formatoMiles.format(Math.round(valor)) : '';
    }

    function obtenerSubtotalCarrito() {
        return consumos.reduce((sum, item) => {
            const producto = productos.find(p => p.id === item.productoId);
            if (!producto) return sum;
            return sum + (producto.precio * item.cantidad);
        }, 0);
    }

    function valorAplicadoRondaActual() {
        const valorBase = parseValorRonda(document.getElementById('valorRonda').value);
        const subtotalPendiente = Math.max(0, obtenerSubtotalCarrito() - subtotalCarritoProcesado);
        return valorBase + subtotalPendiente;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function actualizarJugadoresDesdeFormulario() {
        document.querySelectorAll('.jugador-nombre').forEach(input => {
            const idx = Number(input.dataset.index);
            jugadores[idx].nombre = input.value.trim();
        });

        document.querySelectorAll('.jugador-equipo').forEach(select => {
            const idx = Number(select.dataset.index);
            jugadores[idx].equipo = Number(select.value);
        });

        renderJugadorSelector();
        renderResumen();
    }

    function renderJugadorSelector() {
        const select = document.getElementById('jugadorConsumo');
        const current = select.value;
        select.innerHTML = '';

        const sesionOption = document.createElement('option');
        sesionOption.value = cuentaSesionKey;
        sesionOption.textContent = cuentaSesionLabel;
        select.appendChild(sesionOption);

        jugadores.forEach((jugador, index) => {
            if (!jugador.nombre) {
                return;
            }
            const option = document.createElement('option');
            option.value = String(index);
            option.textContent = `${jugador.nombre} (Equipo ${jugador.equipo})`;
            select.appendChild(option);
        });

        if (current && Array.from(select.options).some(option => option.value === current)) {
            select.value = current;
        } else {
            select.value = cuentaSesionKey;
        }

        document.querySelectorAll('.btn-add-producto').forEach(button => {
            button.disabled = false;
        });
        document.getElementById('ayudaConsumoJugador').textContent = select.value === cuentaSesionKey
            ? 'Este consumo se registra aparte de los jugadores de la mesa.'
            : 'El consumo se suma al jugador seleccionado.';
    }

    function consumoPorProducto(productoId) {
        return consumos
            .filter(item => item.productoId === productoId)
            .reduce((sum, item) => sum + item.cantidad, 0);
    }

    function agregarConsumo(productoId) {
        const selector = document.getElementById('jugadorConsumo');
        if (!selector.value) {
            alert('Selecciona un destino para el consumo.');
            return;
        }
        const esCuentaSesion = selector.value === cuentaSesionKey;
        const jugadorIndex = esCuentaSesion ? null : Number(selector.value);

        if (!esCuentaSesion && (!jugadores[jugadorIndex] || !jugadores[jugadorIndex].nombre)) {
            alert('Solo puedes asignar consumo a jugadores con nombre.');
            return;
        }
        const producto = productos.find(p => p.id === productoId);
        if (!producto) return;

        const stockUsado = consumoPorProducto(productoId);
        if (stockUsado >= producto.stock) {
            alert('No hay stock disponible para este producto.');
            return;
        }

        const existente = consumos.find(c =>
            c.productoId === productoId &&
            c.destino === (esCuentaSesion ? cuentaSesionKey : 'jugador') &&
            c.jugadorIndex === jugadorIndex
        );
        if (existente) {
            existente.cantidad += 1;
        } else {
            consumos.push({
                destino: esCuentaSesion ? cuentaSesionKey : 'jugador',
                jugadorIndex,
                productoId,
                cantidad: 1,
            });
        }

        renderResumen();
    }

    function quitarConsumo(index) {
        consumos.splice(index, 1);
        renderResumen();
    }

    function actualizarCantidad(index, cantidad) {
        const nuevaCantidad = Number(cantidad);
        if (nuevaCantidad < 1 || !Number.isFinite(nuevaCantidad)) {
            quitarConsumo(index);
            return;
        }

        const item = consumos[index];
        const producto = productos.find(p => p.id === item.productoId);
        if (!producto) return;

        const usadosOtros = consumos
            .filter((_, idx) => idx !== index && consumos[idx].productoId === item.productoId)
            .reduce((sum, row) => sum + row.cantidad, 0);

        if (usadosOtros + nuevaCantidad > producto.stock) {
            alert('Cantidad supera el stock disponible.');
            return;
        }

        item.cantidad = nuevaCantidad;
        renderResumen();
    }

    function calcularTotales() {
        const consumosProductos = [0, 0, 0, 0];
        const totalesJugador = [0, 0, 0, 0];

        consumos.forEach(item => {
            const producto = productos.find(p => p.id === item.productoId);
            if (!producto) return;
            if (item.destino === 'jugador' && item.jugadorIndex !== null) {
                consumosProductos[item.jugadorIndex] += producto.precio * item.cantidad;
            }
        });

        jugadores.forEach((jugador, idx) => {
            totalesJugador[idx] = consumosProductos[idx] + rondaAcumuladaPorJugador[idx];
        });

        const subtotalEq1 = totalesJugador.reduce((sum, total, idx) => sum + (jugadores[idx].equipo === 1 ? total : 0), 0);
        const subtotalEq2 = totalesJugador.reduce((sum, total, idx) => sum + (jugadores[idx].equipo === 2 ? total : 0), 0);

        return {
            consumosProductos,
            rondaPorJugador: rondaAcumuladaPorJugador,
            totalesJugador,
            subtotalEq1,
            subtotalEq2,
            totalMesa: subtotalEq1 + subtotalEq2,
        };
    }

    function renderTablaJugadores(totales) {
        const tbody = document.getElementById('tablaJugadores');
        const tfoot = document.getElementById('tablaJugadoresFooter');
        tbody.innerHTML = '';
        tfoot.innerHTML = '';

        jugadores.forEach((jugador, idx) => {
            const nombre = jugador.nombre || `Jugador ${idx + 1}`;
            const consumoSoloProductos = totales.consumosProductos[idx];
            const parteRonda = totales.rondaPorJugador[idx];

            tbody.innerHTML += `
                <tr>
                    <td>${nombre}</td>
                    <td>Equipo ${jugador.equipo}</td>
                    <td class="text-end">${formatoMoneda(consumoSoloProductos)}</td>
                    <td class="text-end">${formatoMoneda(parteRonda)}</td>
                    <td class="text-end"><strong>${formatoMoneda(totales.totalesJugador[idx])}</strong></td>
                </tr>
            `;
        });

        const equipoPerdedorSeleccionado = Number(document.getElementById('perdedorEquipo')?.value || 1);
        const totalEquipoPerdedor = equipoPerdedorSeleccionado === 1 ? totales.subtotalEq1 : totales.subtotalEq2;

        tfoot.innerHTML = `
            <tr class="table-light">
                <th colspan="4" class="text-end">Total Equipo 1</th>
                <th class="text-end">${formatoMoneda(totales.subtotalEq1)}</th>
            </tr>
            <tr class="table-light">
                <th colspan="4" class="text-end">Total Equipo 2</th>
                <th class="text-end">${formatoMoneda(totales.subtotalEq2)}</th>
            </tr>
            <tr class="table-warning">
                <th colspan="4" class="text-end">Total Equipo Perdedor Seleccionado (Equipo ${equipoPerdedorSeleccionado})</th>
                <th class="text-end">${formatoMoneda(totalEquipoPerdedor)}</th>
            </tr>
            <tr class="table-info">
                <th colspan="4" class="text-end">Total General Mesa</th>
                <th class="text-end">${formatoMoneda(totales.totalMesa)}</th>
            </tr>
        `;
    }

    function renderTablaConsumos() {
        const tbody = document.getElementById('tablaConsumos');
        if (consumos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">Sin consumos registrados</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        consumos.forEach((item, index) => {
            const producto = productos.find(p => p.id === item.productoId);
            if (!producto) return;
            const nombreJugador = item.destino === cuentaSesionKey
                ? cuentaSesionLabel
                : (jugadores[item.jugadorIndex]?.nombre || `Jugador ${item.jugadorIndex + 1}`);
            const subtotal = producto.precio * item.cantidad;

            tbody.innerHTML += `
                <tr>
                    <td>${nombreJugador}</td>
                    <td>${producto.nombre}</td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center" style="width: 75px; margin: 0 auto;"
                            min="1" value="${item.cantidad}" onchange="actualizarCantidad(${index}, this.value)">
                    </td>
                    <td class="text-end">${formatoMoneda(subtotal)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarConsumo(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    function renderStocksDisponibles() {
        document.querySelectorAll('.producto-item').forEach(item => {
            const productoId = Number(item.dataset.id);
            const producto = productos.find(p => p.id === productoId);
            const usado = consumoPorProducto(productoId);
            const restante = producto ? Math.max(0, producto.stock - usado) : 0;
            item.querySelector('.stock-view').textContent = restante;
        });
    }

    function validarEquipos() {
        const eq1 = jugadores.filter(j => j.equipo === 1).length;
        const eq2 = jugadores.filter(j => j.equipo === 2).length;

        const valido = eq1 === 2 && eq2 === 2;
        const error = document.getElementById('errorEquipos');

        if (eq1 !== 2 || eq2 !== 2) {
            error.textContent = 'Cada equipo debe tener exactamente 2 jugadores.';
        } else {
            error.textContent = '';
        }

        return valido;
    }

    function actualizarIndicadorRondas() {
        document.getElementById('contadorRondas').textContent = `Rondas: ${rondasCerradas}`;
        document.getElementById('btnAgregarPerdedor').innerHTML = `<i class="bi bi-check-lg"></i> Agregar Perdedor (Ronda ${rondasCerradas + 1})`;
    }

    function agregarPerdedor() {
        actualizarJugadoresDesdeFormulario();
        if (!validarEquipos()) {
            return;
        }

        const valorRonda = valorAplicadoRondaActual();
        if (valorRonda <= 0) {
            alert('Ingresa un valor de ronda mayor que cero.');
            return;
        }

        const equipoPerdedor = Number(document.getElementById('perdedorEquipo').value || 1);
        const indicesPerdedores = jugadores
            .map((jugador, index) => jugador.equipo === equipoPerdedor ? index : null)
            .filter(index => index !== null);

        if (indicesPerdedores.length !== 2) {
            alert('El equipo perdedor debe tener exactamente 2 jugadores.');
            return;
        }

        const valorPorJugador = valorRonda / 2;
        indicesPerdedores.forEach(index => {
            rondaAcumuladaPorJugador[index] += valorPorJugador;
        });

        rondasCerradas += 1;
        historialRondas.push({
            ronda: rondasCerradas,
            equipoPerdedor,
            valorRonda,
            valorPorJugador,
        });
        subtotalCarritoProcesado = obtenerSubtotalCarrito();

        document.getElementById('valorRonda').value = '';
        document.getElementById('okRonda').textContent = `Ronda ${rondasCerradas}: se sumÃ³ ${formatoMoneda(valorRonda)} al Equipo ${equipoPerdedor} (${formatoMoneda(valorPorJugador)} por jugador).`;
        actualizarIndicadorRondas();
        renderTablaRondas();
        renderResumen();
    }

    function renderTablaRondas() {
        const tbody = document.getElementById('tablaRondas');
        if (historialRondas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-2">Sin rondas cerradas</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        historialRondas.forEach(item => {
            tbody.innerHTML += `
                <tr>
                    <td>${item.ronda}</td>
                    <td>Equipo ${item.equipoPerdedor}</td>
                    <td class="text-end">${formatoMoneda(item.valorRonda)}</td>
                    <td class="text-end">${formatoMoneda(item.valorPorJugador)}</td>
                </tr>
            `;
        });
    }

    function renderResumen() {
        const totales = calcularTotales();
        const subtotalCarrito = obtenerSubtotalCarrito();
        const subtotalPendiente = Math.max(0, subtotalCarrito - subtotalCarritoProcesado);
        const valorAplicado = parseValorRonda(document.getElementById('valorRonda').value) + subtotalPendiente;
        renderTablaJugadores(totales);
        renderTablaConsumos();
        renderStocksDisponibles();
        validarEquipos();

        document.getElementById('subtotalCarritoRonda').textContent = formatoMoneda(subtotalPendiente);
        document.getElementById('valorAplicadoRonda').textContent = formatoMoneda(valorAplicado);
        document.getElementById('subtotalEq1').textContent = formatoMoneda(totales.subtotalEq1);
        document.getElementById('subtotalEq2').textContent = formatoMoneda(totales.subtotalEq2);
        document.getElementById('totalMesa').textContent = formatoMoneda(totales.totalMesa);
    }

    document.querySelectorAll('.jugador-nombre, .jugador-equipo').forEach(input => {
        input.addEventListener('input', actualizarJugadoresDesdeFormulario);
        input.addEventListener('change', actualizarJugadoresDesdeFormulario);
    });

    document.getElementById('jugadorConsumo').addEventListener('change', function () {
        const isSesion = this.value === cuentaSesionKey;
        document.getElementById('ayudaConsumoJugador').textContent = isSesion
            ? 'Este consumo se registra aparte de los jugadores de la mesa.'
            : 'El consumo se suma al jugador seleccionado.';
    });
    document.getElementById('valorRonda').addEventListener('input', renderResumen);
    document.getElementById('valorRonda').addEventListener('blur', function () {
        this.value = normalizarValorRonda(this.value);
    });
    document.getElementById('perdedorEquipo').addEventListener('change', renderResumen);
    document.getElementById('buscarProducto').addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.producto-item').forEach(item => {
            const nombre = item.dataset.nombre.toLowerCase();
            item.style.display = nombre.includes(q) ? 'block' : 'none';
        });
    });

    document.querySelectorAll('.btn-add-producto').forEach(button => {
        button.addEventListener('click', function () {
            const item = this.closest('.producto-item');
            agregarConsumo(Number(item.dataset.id));
        });
    });

    window.agregarPerdedor = agregarPerdedor;

    renderJugadorSelector();
    actualizarIndicadorRondas();
    renderTablaRondas();
    renderResumen();
</script>
@endpush


