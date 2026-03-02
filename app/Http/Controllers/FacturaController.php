<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    public function show(int $id)
    {
        $factura = Factura::with([
            'usuario',
            'pedido.detalle_pedidos.producto',
            'pedido.detalle_pedidos.talla',
            'detalles.producto',
            'detalles.talla',
        ])->findOrFail($id);

        abort_if($factura->id_usuario !== Auth::id(), 403);

        return view('facturas.show', compact('factura'));
    }
}
