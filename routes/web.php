<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\ProductoController as AdminProducto;
use App\Http\Controllers\Admin\PedidoController as AdminPedido;
use App\Http\Controllers\Admin\CuponController as AdminCupon;
use App\Http\Controllers\Admin\CategoriaController as AdminCategoria;
use App\Http\Controllers\Admin\EquipoController as AdminEquipo;
use App\Http\Controllers\Admin\DevolucionController as AdminDevolucion;
use App\Http\Controllers\Almacen\ProveedorController as AlmacenProveedor;
use App\Http\Controllers\Almacen\CompraController as AlmacenCompra;
use App\Http\Controllers\Almacen\KardexController as AlmacenKardex;
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

    // ─── Historial de pedidos del cliente ─────────────────────────────────────
    Route::get('/mis-pedidos',                          [DevolucionController::class, 'index'])->name('pedidos.historial');
    Route::get('/mis-pedidos/{id}',                     [DevolucionController::class, 'show'])->name('pedidos.show');

    // ─── Devoluciones del cliente ─────────────────────────────────────────────
    Route::get('/devoluciones/{id_pedido}/crear',       [DevolucionController::class, 'create'])->name('devoluciones.create');
    Route::post('/devoluciones',                        [DevolucionController::class, 'store'])->name('devoluciones.store');
    Route::get('/devoluciones/{id}',                    [DevolucionController::class, 'showDevolucion'])->name('devoluciones.show');
});

// ─── Panel Admin ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',                            [AdminDashboard::class, 'index'])->name('dashboard');

    // Productos (solo editar, desactivar y ver — crear es desde Almacén > Compras)
    Route::get('/productos',                            [AdminProducto::class, 'index'])->name('productos.index');
    Route::get('/productos/{id}/editar',                [AdminProducto::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}',                       [AdminProducto::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}',                    [AdminProducto::class, 'destroy'])->name('productos.destroy');

    // Pedidos
    Route::get('/pedidos',                              [AdminPedido::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id}',                         [AdminPedido::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{id}/estado/{estado}',       [AdminPedido::class, 'actualizarEstado'])->name('pedidos.estado');

    // Devoluciones
    Route::get('/devoluciones',                         [AdminDevolucion::class, 'index'])->name('devoluciones.index');
    Route::get('/devoluciones/{id}',                    [AdminDevolucion::class, 'show'])->name('devoluciones.show');
    Route::patch('/devoluciones/{id}/aprobar',          [AdminDevolucion::class, 'aprobar'])->name('devoluciones.aprobar');
    Route::patch('/devoluciones/{id}/rechazar',         [AdminDevolucion::class, 'rechazar'])->name('devoluciones.rechazar');

    // Cupones
    Route::get('/cupones',                              [AdminCupon::class, 'index'])->name('cupones.index');
    Route::get('/cupones/crear',                        [AdminCupon::class, 'create'])->name('cupones.create');
    Route::post('/cupones',                             [AdminCupon::class, 'store'])->name('cupones.store');
    Route::get('/cupones/{id}/editar',                  [AdminCupon::class, 'edit'])->name('cupones.edit');
    Route::put('/cupones/{id}',                         [AdminCupon::class, 'update'])->name('cupones.update');
    Route::delete('/cupones/{id}',                      [AdminCupon::class, 'destroy'])->name('cupones.destroy');

    // Categorías
    Route::get('/categorias',                           [AdminCategoria::class, 'index'])->name('categorias.index');
    Route::get('/categorias/crear',                     [AdminCategoria::class, 'create'])->name('categorias.create');
    Route::post('/categorias',                          [AdminCategoria::class, 'store'])->name('categorias.store');
    Route::get('/categorias/{id}/editar',               [AdminCategoria::class, 'edit'])->name('categorias.edit');
    Route::put('/categorias/{id}',                      [AdminCategoria::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{id}',                   [AdminCategoria::class, 'destroy'])->name('categorias.destroy');

    // Equipos
    Route::get('/equipos',                              [AdminEquipo::class, 'index'])->name('equipos.index');
    Route::get('/equipos/crear',                        [AdminEquipo::class, 'create'])->name('equipos.create');
    Route::post('/equipos',                             [AdminEquipo::class, 'store'])->name('equipos.store');
    Route::get('/equipos/{id}/editar',                  [AdminEquipo::class, 'edit'])->name('equipos.edit');
    Route::put('/equipos/{id}',                         [AdminEquipo::class, 'update'])->name('equipos.update');
    Route::delete('/equipos/{id}',                      [AdminEquipo::class, 'destroy'])->name('equipos.destroy');
});

// ─── Panel Almacén ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'almacen'])->prefix('almacen')->name('almacen.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.almacen');
    })->name('dashboard');

    // Proveedores
    Route::get('/proveedores',             [AlmacenProveedor::class, 'index'])->name('proveedores.index');
    Route::get('/proveedores/crear',       [AlmacenProveedor::class, 'create'])->name('proveedores.create');
    Route::post('/proveedores',            [AlmacenProveedor::class, 'store'])->name('proveedores.store');
    Route::get('/proveedores/{id}/editar', [AlmacenProveedor::class, 'edit'])->name('proveedores.edit');
    Route::put('/proveedores/{id}',        [AlmacenProveedor::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{id}',     [AlmacenProveedor::class, 'destroy'])->name('proveedores.destroy');

    // Compras
    Route::get('/compras',                 [AlmacenCompra::class, 'index'])->name('compras.index');
    Route::get('/compras/crear',           [AlmacenCompra::class, 'create'])->name('compras.create');
    Route::post('/compras',                [AlmacenCompra::class, 'store'])->name('compras.store');
    Route::get('/compras/{id}',            [AlmacenCompra::class, 'show'])->name('compras.show');
    Route::patch('/compras/{id}/recibir',  [AlmacenCompra::class, 'recibirCompra'])->name('compras.recibir');

    // Kardex
    Route::get('/kardex',                  [AlmacenKardex::class, 'index'])->name('kardex.index');
});
