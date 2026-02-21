<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('usuario')
            ->orderByDesc('fecha_pedido')
            ->paginate(20);

        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with(['usuario', 'detalle_pedidos.producto', 'detalle_pedidos.talla', 'cupon'])
            ->findOrFail($id);

        return view('admin.pedidos.show', compact('pedido'));
    }

    public function actualizarEstado($id, $estado)
    {
        $estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];

        if (!in_array($estado, $estados)) {
            return back()->with('error', 'Estado inválido.');
        }

        $pedido = Pedido::findOrFail($id);
        $pedido->update(['estado_pedido' => $estado]);

        return back()->with('success', "Pedido actualizado a: {$estado}");
    }
}
