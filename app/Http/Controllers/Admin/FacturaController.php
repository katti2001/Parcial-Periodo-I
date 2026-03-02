<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $query = Factura::with(['usuario'])
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado))
            ->when($request->filled('usuario'), fn($q) => $q->where('id_usuario', $request->usuario))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('fecha_emision', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('fecha_emision', '<=', $request->hasta))
            ->orderByDesc('fecha_emision');

        $facturas = $query->paginate(15)->withQueryString();

        return view('admin.facturas.index', compact('facturas'));
    }

    public function show(int $id)
    {
        $factura = Factura::with([
            'usuario',
            'pedido.detalle_pedidos.producto',
            'pedido.detalle_pedidos.talla',
            'detalles.producto',
            'detalles.talla',
        ])->findOrFail($id);

        return view('admin.facturas.show', compact('factura'));
    }
}
