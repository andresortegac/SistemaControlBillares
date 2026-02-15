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


// ✅ Payment model para endpoint de recaudo hoy
use App\Models\Payment;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (login)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Mesas
    Route::resource('mesas', MesaController::class);
    Route::post('/mesas/{mesa}/iniciar', [MesaController::class, 'iniciarUso'])->name('mesas.iniciar');
    Route::post('/mesas/{mesa}/finalizar', [MesaController::class, 'finalizarUso'])->name('mesas.finalizar');
    Route::post('/mesas/{mesa}/pausar', [MesaController::class, 'pausarUso'])->name('mesas.pausar');
    Route::post('/mesas/{mesa}/estado', [MesaController::class, 'cambiarEstado'])->name('mesas.estado');
    
    // Categorías
    Route::resource('categorias', CategoriaController::class);
    
    // Productos
    Route::resource('productos', ProductoController::class);
    Route::get('/productos/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');
    Route::post('/productos/{producto}/stock', [ProductoController::class, 'ajustarStock'])->name('productos.stock');
    
    // Clientes
    Route::resource('clientes', ClienteController::class);
    Route::get('/clientes/buscar', [ClienteController::class, 'buscar'])->name('clientes.buscar');
    Route::post('/clientes/{cliente}/puntos', [ClienteController::class, 'agregarPuntos'])->name('clientes.puntos');
    
    // Ventas
    Route::resource('ventas', VentaController::class);
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::post('/mesas/{mesa}/venta', [VentaController::class, 'ventaMesa'])->name('mesas.venta');
    
    // Reportes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
        Route::get('/productos', [ReporteController::class, 'productos'])->name('productos');
        Route::get('/mesas', [ReporteController::class, 'mesas'])->name('mesas');
        Route::get('/clientes', [ReporteController::class, 'clientes'])->name('clientes');
        Route::get('/dashboard', [ReporteController::class, 'dashboard'])->name('dashboard');
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
