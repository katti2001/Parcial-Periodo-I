<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\AsistenteCatalogoController;
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
use App\Http\Controllers\Almacen\AsistenteController as AlmacenAsistente;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\Admin\FacturaController as AdminFactura;
use App\Http\Controllers\Admin\ReporteController as AdminReporte;
use Illuminate\Support\Facades\Route;

Route::get('/catalogo',         [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/{id}',    [CatalogoController::class, 'show'])->name('catalogo.show');
Route::post('/catalogo/asistente/chat', [AsistenteCatalogoController::class, 'chat'])->name('catalogo.asistente.chat');

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);

    Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro',[AuthController::class, 'registro']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/carrito',                          [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/{id}',                    [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::patch('/carrito/{clave}',                [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::delete('/carrito/{clave}',               [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::delete('/carrito',                       [CarritoController::class, 'vaciar'])->name('carrito.vaciar');

    Route::get('/checkout',                         [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/cupon',                  [CheckoutController::class, 'aplicarCupon'])->name('checkout.cupon');
    Route::post('/checkout/crear-orden',            [CheckoutController::class, 'crearOrden'])->name('checkout.crear-orden');
    Route::post('/checkout/capturar/{orderID}',     [CheckoutController::class, 'capturarOrden'])->name('checkout.capturar');
    Route::get('/checkout/confirmacion/{id}',       [CheckoutController::class, 'confirmacion'])->name('checkout.confirmacion');

    Route::get('/mis-pedidos',                          [DevolucionController::class, 'index'])->name('pedidos.historial');
    Route::get('/mis-pedidos/{id}',                     [DevolucionController::class, 'show'])->name('pedidos.show');

    Route::get('/facturas/{id}',                        [FacturaController::class, 'show'])->name('facturas.show');

    Route::get('/devoluciones/{id_pedido}/crear',       [DevolucionController::class, 'create'])->name('devoluciones.create');
    Route::post('/devoluciones',                        [DevolucionController::class, 'store'])->name('devoluciones.store');
    Route::get('/devoluciones/{id}',                    [DevolucionController::class, 'showDevolucion'])->name('devoluciones.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',                            [AdminDashboard::class, 'index'])->name('dashboard');

    Route::get('/productos',                            [AdminProducto::class, 'index'])->name('productos.index');
    Route::get('/productos/{id}/editar',                [AdminProducto::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}',                       [AdminProducto::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}',                    [AdminProducto::class, 'destroy'])->name('productos.destroy');

    Route::get('/pedidos',                              [AdminPedido::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id}',                         [AdminPedido::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{id}/estado/{estado}',       [AdminPedido::class, 'actualizarEstado'])->name('pedidos.estado');

    Route::get('/devoluciones',                         [AdminDevolucion::class, 'index'])->name('devoluciones.index');
    Route::get('/devoluciones/{id}',                    [AdminDevolucion::class, 'show'])->name('devoluciones.show');
    Route::patch('/devoluciones/{id}/aprobar',          [AdminDevolucion::class, 'aprobar'])->name('devoluciones.aprobar');
    Route::patch('/devoluciones/{id}/rechazar',         [AdminDevolucion::class, 'rechazar'])->name('devoluciones.rechazar');

    Route::get('/facturas',                             [AdminFactura::class, 'index'])->name('facturas.index');
    Route::get('/facturas/{id}',                        [AdminFactura::class, 'show'])->name('facturas.show');

    Route::get('/reportes',                             [AdminReporte::class, 'index'])->name('reportes.index');
    Route::get('/reportes/ventas',                      [AdminReporte::class, 'ventas'])->name('reportes.ventas');
    Route::get('/reportes/productos',                   [AdminReporte::class, 'productos'])->name('reportes.productos');
    Route::get('/reportes/devoluciones',                [AdminReporte::class, 'devoluciones'])->name('reportes.devoluciones');
    Route::get('/reportes/estadisticas', [AdminReporte::class, 'estadisticas'])->name('reportes.estadisticas');

    Route::get('/cupones',                              [AdminCupon::class, 'index'])->name('cupones.index');
    Route::get('/cupones/crear',                        [AdminCupon::class, 'create'])->name('cupones.create');
    Route::post('/cupones',                             [AdminCupon::class, 'store'])->name('cupones.store');
    Route::get('/cupones/{id}/editar',                  [AdminCupon::class, 'edit'])->name('cupones.edit');
    Route::put('/cupones/{id}',                         [AdminCupon::class, 'update'])->name('cupones.update');
    Route::delete('/cupones/{id}',                      [AdminCupon::class, 'destroy'])->name('cupones.destroy');

    Route::get('/categorias',                           [AdminCategoria::class, 'index'])->name('categorias.index');
    Route::get('/categorias/crear',                     [AdminCategoria::class, 'create'])->name('categorias.create');
    Route::post('/categorias',                          [AdminCategoria::class, 'store'])->name('categorias.store');
    Route::get('/categorias/{id}/editar',               [AdminCategoria::class, 'edit'])->name('categorias.edit');
    Route::put('/categorias/{id}',                      [AdminCategoria::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{id}',                   [AdminCategoria::class, 'destroy'])->name('categorias.destroy');

    Route::get('/equipos',                              [AdminEquipo::class, 'index'])->name('equipos.index');
    Route::get('/equipos/crear',                        [AdminEquipo::class, 'create'])->name('equipos.create');
    Route::post('/equipos',                             [AdminEquipo::class, 'store'])->name('equipos.store');
    Route::get('/equipos/{id}/editar',                  [AdminEquipo::class, 'edit'])->name('equipos.edit');
    Route::put('/equipos/{id}',                         [AdminEquipo::class, 'update'])->name('equipos.update');
    Route::delete('/equipos/{id}',                      [AdminEquipo::class, 'destroy'])->name('equipos.destroy');
});

Route::middleware(['auth', 'almacen'])->prefix('almacen')->name('almacen.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.almacen');
    })->name('dashboard');

    Route::get('/proveedores',             [AlmacenProveedor::class, 'index'])->name('proveedores.index');
    Route::get('/proveedores/crear',       [AlmacenProveedor::class, 'create'])->name('proveedores.create');
    Route::post('/proveedores',            [AlmacenProveedor::class, 'store'])->name('proveedores.store');
    Route::get('/proveedores/{id}/editar', [AlmacenProveedor::class, 'edit'])->name('proveedores.edit');
    Route::put('/proveedores/{id}',        [AlmacenProveedor::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{id}',     [AlmacenProveedor::class, 'destroy'])->name('proveedores.destroy');

    Route::get('/compras',                 [AlmacenCompra::class, 'index'])->name('compras.index');
    Route::get('/compras/crear',           [AlmacenCompra::class, 'create'])->name('compras.create');
    Route::post('/compras',                [AlmacenCompra::class, 'store'])->name('compras.store');
    Route::get('/compras/{id}',            [AlmacenCompra::class, 'show'])->name('compras.show');
    Route::patch('/compras/{id}/recibir',  [AlmacenCompra::class, 'recibirCompra'])->name('compras.recibir');

    Route::get('/kardex',                  [AlmacenKardex::class, 'index'])->name('kardex.index');

    Route::post('/asistente/chat',         [AlmacenAsistente::class, 'chat'])->name('asistente.chat');
});
