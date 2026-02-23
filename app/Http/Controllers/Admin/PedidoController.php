<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use App\Models\Kardex;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

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

        $pedido = Pedido::with('detalle_pedidos')->findOrFail($id);

        // Evitar cancelar dos veces
        if ($pedido->estado_pedido === 'cancelado' && $estado === 'cancelado') {
            return back()->with('error', 'El pedido ya está cancelado.');
        }

        DB::transaction(function () use ($pedido, $estado) {
            // Si se cancela, devolver stock
            if ($estado === 'cancelado' && $pedido->estado_pedido !== 'cancelado') {
                foreach ($pedido->detalle_pedidos as $detalle) {
                    // Devolver al lote más reciente del mismo producto/talla
                    $lote = DetalleCompra::where('id_producto', $detalle->id_producto)
                        ->where('id_talla', $detalle->id_talla)
                        ->orderByDesc('id_detalle_compra')
                        ->lockForUpdate()
                        ->first();

                    if ($lote) {
                        $lote->increment('cantidad_restante', $detalle->cantidad);
                    }

                    // Registrar en Kardex como devolución
                    Kardex::create([
                        'id_producto'     => $detalle->id_producto,
                        'id_talla'        => $detalle->id_talla,
                        'tipo_movimiento' => 'compra',
                        'cantidad'        => $detalle->cantidad,
                        'fecha'           => now(),
                        'referencia'      => 'Cancelación Pedido #' . $pedido->id_pedido,
                    ]);
                }
            }

            $pedido->update(['estado_pedido' => $estado]);
        });

        return back()->with('success', "Pedido actualizado a: {$estado}");
    }
}

