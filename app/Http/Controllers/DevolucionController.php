<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\DetalleDevolucion;
use App\Models\Devolucion;
use App\Models\Kardex;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    // ── Historial de pedidos del cliente ────────────────────────────────────────

    public function index()
    {
        $pedidos = Pedido::with(['detalle_pedidos.producto', 'devolucion'])
            ->where('id_usuario', Auth::id())
            ->orderByDesc('fecha_pedido')
            ->paginate(10);

        return view('cliente.pedidos.index', compact('pedidos'));
    }

    // ── Detalle de un pedido ─────────────────────────────────────────────────────

    public function show($id)
    {
        $pedido = Pedido::with([
            'detalle_pedidos.producto.imagenes',
            'detalle_pedidos.talla',
            'cupon',
            'devolucion.detalles',
        ])->where('id_usuario', Auth::id())->findOrFail($id);

        $puedeDevolver = $this->puedesolicitarDevolucion($pedido);

        return view('cliente.pedidos.show', compact('pedido', 'puedeDevolver'));
    }

    // ── Formulario de solicitud de devolución ───────────────────────────────────

    public function create($id_pedido)
    {
        $pedido = Pedido::with(['detalle_pedidos.producto', 'detalle_pedidos.talla'])
            ->where('id_usuario', Auth::id())
            ->findOrFail($id_pedido);

        if (!$this->puedesolicitarDevolucion($pedido)) {
            return redirect()->route('pedidos.show', $id_pedido)
                ->with('error', 'Este pedido no cumple las condiciones para solicitar una devolución.');
        }

        $motivos = Devolucion::MOTIVOS;

        return view('cliente.devoluciones.crear', compact('pedido', 'motivos'));
    }

    // ── Guardar solicitud de devolución ─────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'id_pedido'    => 'required|integer',
            'motivo'       => 'required|in:' . implode(',', array_keys(Devolucion::MOTIVOS)),
            'descripcion'  => 'nullable|string|max:1000',
            'items'        => 'required|array|min:1',
            'items.*.id_detalle_pedido' => 'required|integer',
            'items.*.cantidad_devuelta' => 'required|integer|min:1',
        ]);

        $pedido = Pedido::with('detalle_pedidos')
            ->where('id_usuario', Auth::id())
            ->findOrFail($request->id_pedido);

        // Validar condiciones generales
        if (!$this->puedesolicitarDevolucion($pedido)) {
            return back()->with('error', 'Este pedido no cumple las condiciones para solicitar una devolución.');
        }

        // Validar que cada ítem pertenece al pedido y la cantidad es válida
        $detallesIndexados = $pedido->detalle_pedidos->keyBy('id_detalle_pedido');

        foreach ($request->items as $item) {
            $detalle = $detallesIndexados->get($item['id_detalle_pedido']);

            if (!$detalle) {
                return back()->with('error', 'Uno de los ítems no pertenece a este pedido.');
            }

            if ($item['cantidad_devuelta'] > $detalle->cantidad) {
                return back()->with('error',
                    "La cantidad a devolver del ítem no puede superar la cantidad comprada ({$detalle->cantidad})."
                );
            }
        }

        DB::transaction(function () use ($request, $pedido) {
            $devolucion = Devolucion::create([
                'id_pedido'       => $pedido->id_pedido,
                'id_usuario'      => Auth::id(),
                'estado'          => 'solicitado',
                'motivo'          => $request->motivo,
                'descripcion'     => $request->descripcion,
                'fecha_solicitud' => now(),
            ]);

            foreach ($request->items as $item) {
                DetalleDevolucion::create([
                    'id_devolucion'     => $devolucion->id_devolucion,
                    'id_detalle_pedido' => $item['id_detalle_pedido'],
                    'cantidad_devuelta' => $item['cantidad_devuelta'],
                ]);
            }
        });

        return redirect()->route('pedidos.historial')
            ->with('success', 'Tu solicitud de devolución fue enviada. Te notificaremos cuando sea revisada.');
    }

    // ── Ver estado de una devolución ─────────────────────────────────────────────

    public function showDevolucion($id)
    {
        $devolucion = Devolucion::with([
            'detalles.detallePedido.producto',
            'detalles.detallePedido.talla',
            'pedido',
        ])->where('id_usuario', Auth::id())->findOrFail($id);

        return view('cliente.devoluciones.show', compact('devolucion'));
    }

    // ── Helper privado ───────────────────────────────────────────────────────────

    /**
     * Un pedido puede solicitar devolución si:
     *  - estado_pedido === 'entregado'
     *  - fue entregado hace menos de 30 días
     *  - no tiene ya una devolución en estado 'solicitado' o 'aprobado'
     */
    private function puedesolicitarDevolucion(Pedido $pedido): bool
    {
        if ($pedido->estado_pedido !== 'entregado') {
            return false;
        }

        if ($pedido->fecha_pedido && $pedido->fecha_pedido->diffInDays(now()) > 30) {
            return false;
        }

        $devolucionActiva = $pedido->devolucion
            ?? Devolucion::where('id_pedido', $pedido->id_pedido)
                         ->whereIn('estado', ['solicitado', 'aprobado'])
                         ->exists();

        if ($devolucionActiva) {
            // Si la relación ya está cargada, comparamos estado
            if ($pedido->devolucion && in_array($pedido->devolucion->estado, ['solicitado', 'aprobado'])) {
                return false;
            }
            // Si vino del exists()
            if (is_bool($devolucionActiva) && $devolucionActiva === true) {
                return false;
            }
        }

        return true;
    }
}
