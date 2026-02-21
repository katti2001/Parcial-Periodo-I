<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
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
