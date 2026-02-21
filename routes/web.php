<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\ProductoController as AdminProducto;
use App\Http\Controllers\Admin\PedidoController as AdminPedido;
use App\Http\Controllers\Admin\CuponController as AdminCupon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ─── Catálogo público ─────────────────────────────────────────────────────────
Route::get('/catalogo',         [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/{id}',    [CatalogoController::class, 'show'])->name('catalogo.show');

// ─── Página de inicio ─────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('home');
})->name('home');

// ─── Autenticación (solo para invitados) ──────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);

    Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro',[AuthController::class, 'registro']);
});

// ─── Logout (requiere sesión activa) ──────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Carrito (requiere autenticación) ────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/carrito',                          [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/{id}',                    [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::patch('/carrito/{clave}',                [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::delete('/carrito/{clave}',               [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::delete('/carrito',                       [CarritoController::class, 'vaciar'])->name('carrito.vaciar');

    // ─── Checkout + PayPal ────────────────────────────────────────────────────
    Route::get('/checkout',                         [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/cupon',                  [CheckoutController::class, 'aplicarCupon'])->name('checkout.cupon');
    Route::post('/checkout/crear-orden',            [CheckoutController::class, 'crearOrden'])->name('checkout.crear-orden');
    Route::post('/checkout/capturar/{orderID}',     [CheckoutController::class, 'capturarOrden'])->name('checkout.capturar');
    Route::get('/checkout/confirmacion/{id}',       [CheckoutController::class, 'confirmacion'])->name('checkout.confirmacion');
});

// ─── Panel Admin ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',                            [AdminDashboard::class, 'index'])->name('dashboard');

    // Productos
    Route::get('/productos',                            [AdminProducto::class, 'index'])->name('productos.index');
    Route::get('/productos/crear',                      [AdminProducto::class, 'create'])->name('productos.create');
    Route::post('/productos',                           [AdminProducto::class, 'store'])->name('productos.store');
    Route::get('/productos/{id}/editar',                [AdminProducto::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}',                       [AdminProducto::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}',                    [AdminProducto::class, 'destroy'])->name('productos.destroy');

    // Pedidos
    Route::get('/pedidos',                              [AdminPedido::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id}',                         [AdminPedido::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{id}/estado/{estado}',       [AdminPedido::class, 'actualizarEstado'])->name('pedidos.estado');

    // Cupones
    Route::get('/cupones',                              [AdminCupon::class, 'index'])->name('cupones.index');
    Route::get('/cupones/crear',                        [AdminCupon::class, 'create'])->name('cupones.create');
    Route::post('/cupones',                             [AdminCupon::class, 'store'])->name('cupones.store');
    Route::get('/cupones/{id}/editar',                  [AdminCupon::class, 'edit'])->name('cupones.edit');
    Route::put('/cupones/{id}',                         [AdminCupon::class, 'update'])->name('cupones.update');
    Route::delete('/cupones/{id}',                      [AdminCupon::class, 'destroy'])->name('cupones.destroy');
});

// ─── Panel Almacén ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'almacen'])->prefix('almacen')->name('almacen.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.almacen');
    })->name('dashboard');
});
