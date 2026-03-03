<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Cupon;
use App\Models\DetalleCompra;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    const STOCK_MINIMO = 5;

    public function index()
    {
        $stats = [
            'productos'  => Producto::where('activo', true)->count(),
            'pedidos'    => Pedido::where('estado_pedido', '!=', 'cancelado')->count(),
            'usuarios'   => Usuario::where('rol', 'cliente')->count(),
            'categorias' => Categoria::count(),
            'ingresos'   => Pedido::where('estado_pago', 'pagado')
                                ->where('estado_pedido', '!=', 'cancelado')
                                ->sum('total'),
        ];

        $pedidos_recientes = Pedido::with('usuario')
            ->orderByDesc('fecha_pedido')
            ->limit(5)
            ->get();

        $productos_stock_bajo = Producto::where('activo', true)
            ->withSum('detalle_compras as stock_total', 'cantidad_restante')
            ->having('stock_total', '<=', self::STOCK_MINIMO)
            ->orderBy('stock_total')
            ->get();

        return view('admin.dashboard', compact('stats', 'pedidos_recientes', 'productos_stock_bajo'));
    }
}
