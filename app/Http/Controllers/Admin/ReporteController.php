<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetalleFactura;
use App\Models\Devolucion;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Usuario;
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
        $desde  = $request->filled('desde')  ? $request->desde  : now()->startOfMonth()->toDateString();
        $hasta  = $request->filled('hasta')  ? $request->hasta  : now()->toDateString();
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

    public function estadisticas()
    {
        $kpis = [
            'ingresos_mes'     => Factura::whereIn('estado', ['pagada', 'emitida'])
                                    ->whereMonth('fecha_emision', now()->month)
                                    ->whereYear('fecha_emision',  now()->year)
                                    ->sum('total'),
            'ingresos_total'   => Factura::whereIn('estado', ['pagada', 'emitida'])->sum('total'),
            'pedidos_mes'      => Pedido::whereMonth('fecha_pedido', now()->month)
                                    ->whereYear('fecha_pedido',  now()->year)
                                    ->count(),
            'clientes_total'   => Usuario::where('rol', 'cliente')->count(),
            'devoluciones_mes' => Devolucion::whereMonth('fecha_solicitud', now()->month)
                                    ->whereYear('fecha_solicitud',  now()->year)
                                    ->count(),
            'ticket_promedio'  => Pedido::where('estado_pago', 'pagado')->avg('total') ?? 0,
        ];

        $rawMeses = Factura::select(
                DB::raw("DATE_FORMAT(fecha_emision, '%Y-%m') as mes"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->whereIn('estado', ['pagada', 'emitida'])
            ->where('fecha_emision', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $meses[$key] = [
                'label'    => now()->subMonths($i)->locale('es')->isoFormat('MMM YYYY'),
                'total'    => (float)($rawMeses->get($key)?->total    ?? 0),
                'cantidad' => (int)(  $rawMeses->get($key)?->cantidad ?? 0),
            ];
        }
        $maxVenta = max(array_column($meses, 'total')) ?: 1;

        $pedidosPorEstado = Pedido::select('estado_pedido', DB::raw('COUNT(*) as cantidad'))
            ->whereNotNull('estado_pedido')
            ->groupBy('estado_pedido')
            ->orderByDesc('cantidad')
            ->get();
        $totalPedidos = $pedidosPorEstado->sum('cantidad') ?: 1;

        $topProductos = DetalleFactura::select(
                'id_producto',
                DB::raw('SUM(cantidad) as total_unidades'),
                DB::raw('SUM(total_linea) as total_ingresos')
            )
            ->with('producto:id_producto,nombre')
            ->groupBy('id_producto')
            ->orderByDesc('total_unidades')
            ->limit(8)
            ->get();
        $maxUnidades = $topProductos->max('total_unidades') ?: 1;

        $devolucionesPorMotivo = Devolucion::select('motivo', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('motivo')
            ->orderByDesc('cantidad')
            ->get()
            ->map(fn($d) => [
                'label'    => Devolucion::MOTIVOS[$d->motivo] ?? $d->motivo,
                'cantidad' => $d->cantidad,
            ]);
        $totalDevoluciones = $devolucionesPorMotivo->sum('cantidad') ?: 1;

        return view('admin.reportes.estadisticas', compact(
            'kpis', 'meses', 'maxVenta',
            'pedidosPorEstado', 'totalPedidos',
            'topProductos', 'maxUnidades',
            'devolucionesPorMotivo', 'totalDevoluciones'
        ));
    }
}