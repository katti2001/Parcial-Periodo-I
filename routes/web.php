<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CheckoutController;
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
    Route::get('/dashboard', function () {
        return view('dashboard.admin');
    })->name('dashboard');
});

// ─── Panel Almacén ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'almacen'])->prefix('almacen')->name('almacen.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.almacen');
    })->name('dashboard');
});
