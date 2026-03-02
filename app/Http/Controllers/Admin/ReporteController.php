<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetalleFactura;
use App\Models\DetallePedido;
use App\Models\Devolucion;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    // ── Índice de reportes ──────────────────────────────────────────────────────
    public function index()
    {
        return view('admin.reportes.index');
    }

    // ── Reporte 1: Ventas por período ───────────────────────────────────────────
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

    // ── Reporte 2: Productos más vendidos ───────────────────────────────────────
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

    // ── Reporte 3: Devoluciones ─────────────────────────────────────────────────
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
            'cantidad'         => $devoluciones->count(),
            'monto_reembolso'  => $devoluciones->sum('monto_reembolso'),
            'aprobadas'        => $devoluciones->where('estado', 'aprobado')->count(),
            'rechazadas'       => $devoluciones->where('estado', 'rechazado')->count(),
            'solicitadas'      => $devoluciones->where('estado', 'solicitado')->count(),
        ];

        return view('admin.reportes.devoluciones', compact('devoluciones', 'totales', 'desde', 'hasta', 'estado'));
    }

    // ── Estadísticas generales ──────────────────────────────────────────────────
    public function estadisticas()
    {
        // Ventas por mes — últimos 12 meses
        $ventasPorMes = Factura::select(
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

        // Rellenar todos los meses aunque no tengan datos
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $meses[$key] = [
                'label'    => now()->subMonths($i)->locale('es')->isoFormat('MMM YYYY'),
                'total'    => (float) ($ventasPorMes->get($key)?->total ?? 0),
                'cantidad' => (int)   ($ventasPorMes->get($key)?->cantidad ?? 0),
            ];
        }
        $maxVenta = max(array_column($meses, 'total')) ?: 1;

        // Pedidos por estado
        $pedidosPorEstado = Pedido::select('estado_pedido', DB::raw('COUNT(*) as cantidad'))
            ->whereNotNull('estado_pedido')
            ->groupBy('estado_pedido')
            ->orderByDesc('cantidad')
            ->get();
        $totalPedidos = $pedidosPorEstado->sum('cantidad') ?: 1;

        // Top 8 productos más vendidos (todos los tiempos)
        $topProductos = DetallePedido::select(
                'id_producto',
                DB::raw('SUM(cantidad) as total_unidades'),
                DB::raw('SUM(cantidad * precio_unitario) as total_ingresos')
            )
            ->with('producto:id_producto,nombre')
            ->groupBy('id_producto')
            ->orderByDesc('total_unidades')
            ->limit(8)
            ->get();
        $maxUnidades = $topProductos->max('total_unidades') ?: 1;

        // Devoluciones por motivo
        $devolucionesPorMotivo = Devolucion::select('motivo', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('motivo')
            ->orderByDesc('cantidad')
            ->get()
            ->map(fn($row) => [
                'label'    => Devolucion::MOTIVOS[$row->motivo] ?? $row->motivo,
                'cantidad' => $row->cantidad,
            ]);
        $totalDevoluciones = $devolucionesPorMotivo->sum('cantidad') ?: 1;

        // Nuevos clientes por mes — últimos 12 meses
        $clientesRaw = Usuario::select(
                DB::raw("DATE_FORMAT(fecha_registro, '%Y-%m') as mes"),
                DB::raw('COUNT(*) as cantidad')
            )
            ->where('rol', 'cliente')
            ->where('fecha_registro', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('mes')
            ->pluck('cantidad', 'mes');

        foreach ($meses as $key => &$m) {
            $m['clientes'] = (int) ($clientesRaw[$key] ?? 0);
        }
        unset($m);
        $maxClientes = max(array_column($meses, 'clientes')) ?: 1;

        // KPIs resumen
        $kpis = [
            'ingresos_mes'     => Factura::whereIn('estado', ['pagada', 'emitida'])
                                      ->whereMonth('fecha_emision', now()->month)
                                      ->whereYear('fecha_emision', now()->year)
                                      ->sum('total'),
            'ingresos_total'   => Factura::whereIn('estado', ['pagada', 'emitida'])->sum('total'),
            'pedidos_mes'      => Pedido::whereMonth('fecha_pedido', now()->month)
                                      ->whereYear('fecha_pedido', now()->year)
                                      ->count(),
            'clientes_total'   => Usuario::where('rol', 'cliente')->count(),
            'devoluciones_mes' => Devolucion::whereMonth('fecha_solicitud', now()->month)
                                      ->whereYear('fecha_solicitud', now()->year)
                                      ->count(),
            'ticket_promedio'  => Pedido::where('estado_pago', 'pagado')->avg('total') ?? 0,
        ];

        return view('admin.reportes.estadisticas', compact(
            'meses', 'maxVenta',
            'pedidosPorEstado', 'totalPedidos',
            'topProductos', 'maxUnidades',
            'devolucionesPorMotivo', 'totalDevoluciones',
            'maxClientes', 'kpis'
        ));
    }
}
