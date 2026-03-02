<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetalleFactura;
use App\Models\Devolucion;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        return view('admin.reportes.index');
    }

    public function ventas(Request $request)
    {
        $desde = $request->filled('desde') ? $request->desde : now()->startOfMonth()->toDateString();
        $hasta = $request->filled('hasta') ? $request->hasta : now()->toDateString();

        $facturas = Factura::with(['usuario'])
            ->whereIn('estado', ['pagada', 'emitida'])
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->orderBy('fecha_emision')
            ->get();

        $totales = [
            'subtotal'    => $facturas->sum('subtotal'),
            'descuento'   => $facturas->sum('descuento'),
            'impuesto'    => $facturas->sum('impuesto'),
            'costo_envio' => $facturas->sum('costo_envio'),
            'total'       => $facturas->sum('total'),
            'cantidad'    => $facturas->count(),
        ];

        return view('admin.reportes.ventas', compact('facturas', 'totales', 'desde', 'hasta'));
    }

    public function productos(Request $request)
    {
        $desde = $request->filled('desde') ? $request->desde : now()->startOfMonth()->toDateString();
        $hasta = $request->filled('hasta') ? $request->hasta : now()->toDateString();

        $productos = DetalleFactura::select(
                'id_producto',
                DB::raw('SUM(cantidad) as total_unidades'),
                DB::raw('SUM(total_linea) as total_ingresos')
            )
            ->whereHas('factura', function ($q) use ($desde, $hasta) {
                $q->whereIn('estado', ['pagada', 'emitida'])
                  ->whereDate('fecha_emision', '>=', $desde)
                  ->whereDate('fecha_emision', '<=', $hasta);
            })
            ->with('producto')
            ->groupBy('id_producto')
            ->orderByDesc('total_unidades')
            ->get();

        return view('admin.reportes.productos', compact('productos', 'desde', 'hasta'));
    }

    public function devoluciones(Request $request)
    {
        $desde = $request->filled('desde') ? $request->desde : now()->startOfMonth()->toDateString();
        $hasta = $request->filled('hasta') ? $request->hasta : now()->toDateString();
        $estado = $request->filled('estado') ? $request->estado : null;

        $devoluciones = Devolucion::with(['usuario', 'pedido'])
            ->whereDate('fecha_solicitud', '>=', $desde)
            ->whereDate('fecha_solicitud', '<=', $hasta)
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->orderBy('fecha_solicitud')
            ->get();

        $totales = [
            'cantidad'        => $devoluciones->count(),
            'monto_reembolso' => $devoluciones->sum('monto_reembolso'),
            'aprobadas'       => $devoluciones->where('estado', 'aprobado')->count(),
            'rechazadas'      => $devoluciones->where('estado', 'rechazado')->count(),
            'solicitadas'     => $devoluciones->where('estado', 'solicitado')->count(),
        ];

        return view('admin.reportes.devoluciones', compact('devoluciones', 'totales', 'desde', 'hasta', 'estado'));
    }
}
