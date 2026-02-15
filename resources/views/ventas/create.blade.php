@extends('layouts.app')

@section('title', 'Nueva Venta')

@push('styles')
<style>
    .producto-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .producto-item:hover {
        background-color: #f8f9fa;
    }
    .venta-item {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cart-plus"></i> Nueva Venta</h2>
    <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
        <i class="bi bi-x-lg"></i> Cancelar
    </a>
</div>

<form action="{{ route('ventas.store') }}" method="POST" id="ventaForm">
    @csrf
    
    @if($usoMesa)
    <input type="hidden" name="uso_mesa_id" value="{{ $usoMesa->id }}">
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <strong>Mesa:</strong> {{ $usoMesa->mesa->nombre }} | 
        <strong>Tiempo:</strong> <span id="tiempoMesa">{{ $usoMesa->getTiempoTranscurrido()['formateado'] }}</span> | 
        <strong>Total:</strong> $<span id="totalMesa">{{ number_format($usoMesa->getCostoActual(), 2) }}</span>
    </div>
    @endif

    <div class="row">
        <!-- Panel de Productos -->
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-search"></i> Buscar Productos</h5>
                </div>
                <div class="card-body">
                    <input type="text" id="buscarProducto" class="form-control mb-3" placeholder="Escribe para buscar...">
                    
                    <div id="listaProductos" style="max-height: 400px; overflow-y: auto;">
                        @foreach($productos as $producto)
                        <div class="producto-item d-flex justify-content-between align-items-center p-2 border-bottom" 
                             data-id="{{ $producto->id }}"
                             data-nombre="{{ $producto->nombre }}"
                             data-precio="{{ $producto->precio_venta }}"
                             data-stock="{{ $producto->stock }}">
                            <div>
                                <strong>{{ $producto->nombre }}</strong>
                                <br><small class="text-muted">Stock: {{ $producto->stock }}</small>
                            </div>
                            <div class="text-end">
                                <strong>${{ number_format($producto->precio_venta, 2) }}</strong>
                                <button type="button" class="btn btn-sm btn-primary ms-2" onclick="agregarProducto({{ $producto->id }})">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel de Venta -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Detalle de Venta</h5>
                </div>
                <div class="card-body">
                    <!-- Cliente -->
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select" id="clienteSelect">
                            <option value="">Cliente General</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" data-membresia="{{ $cliente->descuento_membresia }}">
                                {{ $cliente->nombre }} @if($cliente->descuento_membresia > 0) ({{ $cliente->descuento_membresia }}% desc.) @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Tipo de Venta -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de Venta</label>
                        <select name="tipo" class="form-select" id="tipoVenta">
                            <option value="productos" {{ $usoMesa ? '' : 'selected' }}>Productos</option>
                            @if($usoMesa)
                            <option value="mixta" selected>Mixta (Mesa + Productos)</option>
                            @endif
                        </select>
                    </div>
                    
                    <!-- Productos Agregados -->
                    <div id="productosAgregados" class="mb-3">
                        <label class="form-label">Productos</label>
                        <div id="listaVenta">
                            <!-- Aquí se agregan los productos dinámicamente -->
                        </div>
                        @if($usoMesa)
                        <div class="venta-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Uso de {{ $usoMesa->mesa->nombre }}</strong>
                                <br><small class="text-muted">{{ $usoMesa->getTiempoTranscurrido()['total_minutos'] }} minutos</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <strong class="me-3">${{ number_format($usoMesa->getCostoActual(), 2) }}</strong>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Totales -->
                    <div class="border-top pt-3">
                        <div class="row mb-2">
                            <div class="col-6 text-end">Subtotal:</div>
                            <div class="col-6 text-end"><strong>$<span id="subtotal">0.00</span></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Descuento:</div>
                            <div class="col-6 text-end">
                                <div class="input-group input-group-sm justify-content-end">
                                    <input type="number" name="descuento" id="descuento" class="form-control text-end" value="0" min="0" step="0.01" style="max-width: 100px;">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6 text-end"><h4>Total:</h4></div>
                            <div class="col-6 text-end"><h4>$<span id="total">0.00</span></h4></div>
                        </div>
                    </div>
                    
                    <!-- Método de Pago -->
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select name="metodo_pago" class="form-select" id="metodoPago">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="mixto">Mixto</option>
                        </select>
                    </div>
                    
                    <!-- Efectivo Recibido -->
                    <div class="mb-3" id="efectivoContainer">
                        <label class="form-label">Efectivo Recibido</label>
                        <input type="number" name="efectivo_recibido" id="efectivoRecibido" class="form-control" step="0.01" min="0">
                        <div class="text-end mt-1">
                            <small class="text-muted">Cambio: $<span id="cambio">0.00</span></small>
                        </div>
                    </div>
                    
                    <!-- Notas -->
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 btn-lg" id="btnFinalizar" disabled>
                        <i class="bi bi-check-lg"></i> Finalizar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let productos = @json($productos);
    let productosEnVenta = [];
    let usoMesa = @json($usoMesa);
    
    function agregarProducto(productoId) {
        const producto = productos.find(p => p.id == productoId);
        if (!producto) return;
        
        const existente = productosEnVenta.find(p => p.id == productoId);
        if (existente) {
            if (existente.cantidad < producto.stock) {
                existente.cantidad++;
            } else {
                alert('No hay suficiente stock');
                return;
            }
        } else {
            productosEnVenta.push({
                id: producto.id,
                nombre: producto.nombre,
                precio: producto.precio_venta,
                cantidad: 1,
                stock: producto.stock
            });
        }
        
        renderizarVenta();
    }
    
    function quitarProducto(index) {
        productosEnVenta.splice(index, 1);
        renderizarVenta();
    }
    
    function actualizarCantidad(index, cantidad) {
        const producto = productosEnVenta[index];
        if (cantidad > producto.stock) {
            alert('No hay suficiente stock');
            return;
        }
        if (cantidad < 1) {
            quitarProducto(index);
            return;
        }
        producto.cantidad = parseInt(cantidad);
        renderizarVenta();
    }
    
    function renderizarVenta() {
        const contenedor = document.getElementById('listaVenta');
        contenedor.innerHTML = '';
        
        productosEnVenta.forEach((producto, index) => {
            const html = `
                <div class="venta-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${producto.nombre}</strong>
                        <br><small class="text-muted">$${parseFloat(producto.precio).toFixed(2)} c/u</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="hidden" name="productos[${index}][id]" value="${producto.id}">
                        <input type="hidden" name="productos[${index}][precio]" value="${producto.precio}">
                        <input type="number" name="productos[${index}][cantidad]" 
                               class="form-control form-control-sm text-center" 
                               value="${producto.cantidad}" min="1" max="${producto.stock}"
                               onchange="actualizarCantidad(${index}, this.value)"
                               style="width: 60px;">
                        <strong class="mx-3">$${(producto.precio * producto.cantidad).toFixed(2)}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="quitarProducto(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            contenedor.innerHTML += html;
        });
        
        calcularTotales();
    }
    
    function calcularTotales() {
        let subtotalProductos = productosEnVenta.reduce((sum, p) => sum + (p.precio * p.cantidad), 0);
        let subtotalMesa = usoMesa ? parseFloat(usoMesa.costo_actual || usoMesa.total || 0) : 0;
        let subtotal = subtotalProductos + subtotalMesa;
        
        // Aplicar descuento de membresía
        const clienteSelect = document.getElementById('clienteSelect');
        const descuentoMembresia = clienteSelect.selectedOptions[0]?.dataset.membresia || 0;
        let descuento = parseFloat(document.getElementById('descuento').value) || 0;
        
        if (descuentoMembresia > 0) {
            descuento += (subtotal * descuentoMembresia / 100);
        }
        
        let total = subtotal - descuento;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('total').textContent = total.toFixed(2);
        
        // Habilitar/deshabilitar botón
        document.getElementById('btnFinalizar').disabled = productosEnVenta.length === 0 && !usoMesa;
    }
    
    // Buscar productos
    document.getElementById('buscarProducto').addEventListener('input', function() {
        const busqueda = this.value.toLowerCase();
        const items = document.querySelectorAll('.producto-item');
        
        items.forEach(item => {
            const nombre = item.dataset.nombre.toLowerCase();
            if (nombre.includes(busqueda)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Cambio
    document.getElementById('efectivoRecibido').addEventListener('input', function() {
        const recibido = parseFloat(this.value) || 0;
        const total = parseFloat(document.getElementById('total').textContent);
        const cambio = recibido - total;
        document.getElementById('cambio').textContent = cambio > 0 ? cambio.toFixed(2) : '0.00';
    });
    
    // Método de pago
    document.getElementById('metodoPago').addEventListener('change', function() {
        const efectivoContainer = document.getElementById('efectivoContainer');
        if (this.value === 'efectivo' || this.value === 'mixto') {
            efectivoContainer.style.display = 'block';
        } else {
            efectivoContainer.style.display = 'none';
        }
    });
    
    // Descuento
    document.getElementById('descuento').addEventListener('input', calcularTotales);
    
    // Cliente (descuento membresía)
    document.getElementById('clienteSelect').addEventListener('change', calcularTotales);
    
    // Actualizar timer de mesa
    @if($usoMesa)
    setInterval(() => {
        // Aquí se actualizaría el tiempo en tiempo real
    }, 1000);
    @endif
</script>
@endpush
