<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use App\Models\Devolucion;
use App\Models\Kardex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    // ── Lista de solicitudes ─────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Devolucion::with(['usuario', 'pedido'])
            ->orderByRaw("FIELD(estado, 'solicitado', 'aprobado', 'rechazado')")
            ->orderByDesc('fecha_solicitud');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $devoluciones = $query->paginate(20)->withQueryString();

        $totales = [
            'solicitado' => Devolucion::solicitado()->count(),
            'aprobado'   => Devolucion::aprobado()->count(),
            'rechazado'  => Devolucion::rechazado()->count(),
        ];

        return view('admin.devoluciones.index', compact('devoluciones', 'totales'));
    }

    // ── Detalle de una solicitud ─────────────────────────────────────────────────

    public function show($id)
    {
        $devolucion = Devolucion::with([
            'usuario',
            'pedido',
            'detalles.detallePedido.producto',
            'detalles.detallePedido.talla',
        ])->findOrFail($id);

        return view('admin.devoluciones.show', compact('devolucion'));
    }

    // ── Aprobar devolución ───────────────────────────────────────────────────────

    public function aprobar(Request $request, $id)
    {
        $request->validate([
            'notas_admin'      => 'nullable|string|max:1000',
            'paypal_refund_id' => 'nullable|string|max:255',
        ]);

        $devolucion = Devolucion::with([
            'detalles.detallePedido',
        ])->findOrFail($id);

        if ($devolucion->estado !== 'solicitado') {
            return back()->with('error', 'Solo se pueden aprobar solicitudes en estado "solicitado".');
        }

        DB::transaction(function () use ($devolucion, $request) {

            // 1. Calcular monto de reembolso proporcional a los ítems devueltos
            $montoReembolso = 0;
            foreach ($devolucion->detalles as $detalle) {
                $dp = $detalle->detallePedido;
                $montoReembolso += $detalle->cantidad_devuelta * $dp->precio_unitario;
            }

            // 2. Restaurar stock solo si el motivo NO es producto_defectuoso
            if ($devolucion->regresaAlStock()) {
                foreach ($devolucion->detalles as $detalle) {
                    $dp = $detalle->detallePedido;

                    // Devolver al lote FIFO más reciente del mismo producto/talla
                    $lote = DetalleCompra::where('id_producto', $dp->id_producto)
                        ->where('id_talla', $dp->id_talla)
                        ->orderByDesc('id_detalle_compra')
                        ->lockForUpdate()
                        ->first();

                    if ($lote) {
                        $lote->increment('cantidad_restante', $detalle->cantidad_devuelta);
                    }

                    // Registrar en Kardex como entrada por devolución
                    Kardex::create([
                        'id_producto'     => $dp->id_producto,
                        'id_talla'        => $dp->id_talla,
                        'tipo_movimiento' => 'compra',
                        'cantidad'        => $detalle->cantidad_devuelta,
                        'fecha'           => now(),
                        'referencia'      => 'Devolución Aprobada #' . $devolucion->id_devolucion
                                           . ' (Pedido #' . $devolucion->id_pedido . ')',
                    ]);
                }
            }
            // Si es producto_defectuoso: no toca inventario, el producto está dañado.

            // 3. Actualizar estado de la devolución
            $devolucion->update([
                'estado'           => 'aprobado',
                'monto_reembolso'  => round($montoReembolso, 2),
                'paypal_refund_id' => $request->paypal_refund_id,
                'notas_admin'      => $request->notas_admin,
                'fecha_resolucion' => now(),
            ]);
        });

        return redirect()->route('admin.devoluciones.show', $id)
            ->with('success', 'Devolución aprobada correctamente.');
    }

    // ── Rechazar devolución ──────────────────────────────────────────────────────

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'notas_admin' => 'required|string|max:1000',
        ]);

        $devolucion = Devolucion::findOrFail($id);

        if ($devolucion->estado !== 'solicitado') {
            return back()->with('error', 'Solo se pueden rechazar solicitudes en estado "solicitado".');
        }

        $devolucion->update([
            'estado'           => 'rechazado',
            'notas_admin'      => $request->notas_admin,
            'fecha_resolucion' => now(),
        ]);

        return redirect()->route('admin.devoluciones.show', $id)
            ->with('success', 'Devolución rechazada.');
    }
}
