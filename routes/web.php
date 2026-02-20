<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\CuentaMesaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (login)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/', [AuthController::class, 'login'])->name('login.root_post');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Mesas (operación para cajero/gerente/admin)
    Route::resource('mesas', MesaController::class)->only(['index', 'show']);
    Route::post('/mesas/{mesa}/iniciar', [MesaController::class, 'iniciarUso'])->name('mesas.iniciar');
    Route::post('/mesas/{mesa}/finalizar', [MesaController::class, 'finalizarUso'])->name('mesas.finalizar');
    Route::post('/mesas/{mesa}/pausar', [MesaController::class, 'pausarUso'])->name('mesas.pausar');
    Route::post('/mesas/{mesa}/reanudar', [MesaController::class, 'reanudarUso'])->name('mesas.reanudar');
    Route::post('/mesas/{mesa}/cerrar-partida', [MesaController::class, 'cerrarPartida'])->name('mesas.cerrar_partida');
    Route::post('/mesas/{mesa}/cuentas', [CuentaMesaController::class, 'store'])->name('mesas.cuentas.store');

    Route::prefix('cuentas-mesa')->name('cuentas_mesa.')->group(function () {
        Route::get('/{cuentaMesa}', [CuentaMesaController::class, 'show'])->name('show');
        Route::post('/{cuentaMesa}/jugadores', [CuentaMesaController::class, 'storeJugador'])->name('jugadores.store');
        Route::patch('/{cuentaMesa}/jugadores/{jugadorMesa}/inactivar', [CuentaMesaController::class, 'inactivarJugador'])->name('jugadores.inactivar');
        Route::post('/{cuentaMesa}/consumos', [CuentaMesaController::class, 'storeConsumo'])->name('consumos.store');
        Route::post('/{cuentaMesa}/rondas', [CuentaMesaController::class, 'registrarRonda'])->name('rondas.store');
        Route::post('/{cuentaMesa}/jugadores/{jugadorMesa}/pagos', [CuentaMesaController::class, 'registrarPago'])->name('pagos.store');
        Route::post('/{cuentaMesa}/cerrar', [CuentaMesaController::class, 'cerrar'])->name('cerrar');
    });

    // Ventas (operación)
    Route::resource('ventas', VentaController::class)->except(['destroy']);
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::post('/mesas/{mesa}/venta', [VentaController::class, 'ventaMesa'])->name('mesas.venta');

    // Módulos de gestión (solo gerente/admin)
    Route::middleware(['can:gerente'])->group(function () {
        Route::resource('mesas', MesaController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::post('/mesas/{mesa}/estado', [MesaController::class, 'cambiarEstado'])->name('mesas.estado');

        Route::resource('categorias', CategoriaController::class);

        Route::resource('productos', ProductoController::class);
        Route::get('/productos/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');
        Route::post('/productos/{producto}/stock', [ProductoController::class, 'ajustarStock'])->name('productos.stock');

        Route::resource('clientes', ClienteController::class);
        Route::get('/clientes/buscar', [ClienteController::class, 'buscar'])->name('clientes.buscar');
        Route::post('/clientes/{cliente}/puntos', [ClienteController::class, 'agregarPuntos'])->name('clientes.puntos');

        Route::delete('/ventas/{venta}', [VentaController::class, 'destroy'])->name('ventas.destroy');

        Route::get('/gastos/export/csv', [GastoController::class, 'exportCsv'])->name('gastos.export.csv');
        Route::resource('gastos', GastoController::class);

        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
            Route::get('/productos', [ReporteController::class, 'productos'])->name('productos');
            Route::get('/mesas', [ReporteController::class, 'mesas'])->name('mesas');
            Route::get('/clientes', [ReporteController::class, 'clientes'])->name('clientes');
            Route::get('/gastos', [GastoController::class, 'reporteMensual'])->name('gastos');
            Route::get('/dashboard', [ReporteController::class, 'dashboard'])->name('dashboard');
        });
    });

    // Usuarios (solo admin)
    Route::middleware(['can:admin'])->group(function () {
        Route::resource('usuarios', UsuarioController::class);
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    });

    // Cambiar contraseña
    Route::post('/password/cambiar', [AuthController::class, 'cambiarPassword'])->name('password.cambiar');
});
