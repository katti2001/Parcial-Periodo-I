<?php

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Kardex;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Talla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor')
            ->orderByDesc('id_compra')
            ->paginate(15);

        return view('almacen.compras.index', compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre_empresa')->get();
        $productos   = Producto::where('activo', true)->orderBy('nombre')->get();
        $tallas      = Talla::orderBy('nombre')->get();

        return view('almacen.compras.create', compact('proveedores', 'productos', 'tallas'));
    }

    /**
     * Registrar una compra con sus detalles.
     * Actualiza cantidad_restante e inserta en kardex dentro de una transacción.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_proveedor'              => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'              => 'required|date',
            'numero_factura_proveedor'  => 'nullable|string|max:50',
            'estado'                    => 'required|in:solicitado,recibido,cancelado',
            'items'                     => 'required|array|min:1',
            'items.*.id_producto'       => 'required|exists:productos,id_producto',
            'items.*.id_talla'          => 'required|exists:tallas,id_talla',
            'items.*.cantidad_comprada' => 'required|integer|min:1',
            'items.*.costo_unitario'    => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Calcular total
            $total = collect($request->items)
                ->sum(fn($i) => $i['cantidad_comprada'] * $i['costo_unitario']);

            $compra = Compra::create([
                'id_proveedor'             => $request->id_proveedor,
                'fecha_compra'             => $request->fecha_compra,
                'total_compra'             => $total,
                'numero_factura_proveedor' => $request->numero_factura_proveedor,
                'estado'                   => $request->estado,
            ]);

            foreach ($request->items as $item) {
                $detalle = DetalleCompra::create([
                    'id_compra'         => $compra->id_compra,
                    'id_producto'       => $item['id_producto'],
                    'id_talla'          => $item['id_talla'],
                    'cantidad_comprada' => $item['cantidad_comprada'],
                    'cantidad_restante' => $item['cantidad_comprada'], // inicia igual
                    'costo_unitario'    => $item['costo_unitario'],
                ]);

                // Solo insertar en kardex si el estado es "recibido"
                if ($request->estado === 'recibido') {
                    Kardex::create([
                        'id_producto'     => $item['id_producto'],
                        'id_talla'        => $item['id_talla'],
                        'tipo_movimiento' => 'compra',
                        'cantidad'        => $item['cantidad_comprada'],
                        'fecha'           => now(),
                        'referencia'      => 'Compra #' . $compra->id_compra,
                    ]);
                }
            }
        });

        return redirect()->route('almacen.compras.index')
            ->with('success', 'Compra registrada correctamente.');
    }

    public function show($id)
    {
        $compra = Compra::with([
            'proveedor',
            'detalle_compras.producto',
            'detalle_compras.talla',
        ])->findOrFail($id);

        return view('almacen.compras.show', compact('compra'));
    }

    /**
     * Marcar una compra como "recibida" e insertar en kardex si aún no se hizo.
     */
    public function recibirCompra($id)
    {
        $compra = Compra::with('detalle_compras')->findOrFail($id);

        if ($compra->estado === 'recibido') {
            return redirect()->route('almacen.compras.show', $id)
                ->with('error', 'Esta compra ya fue marcada como recibida.');
        }

        DB::transaction(function () use ($compra) {
            $compra->update(['estado' => 'recibido']);

            foreach ($compra->detalle_compras as $detalle) {
                Kardex::create([
                    'id_producto'     => $detalle->id_producto,
                    'id_talla'        => $detalle->id_talla,
                    'tipo_movimiento' => 'compra',
                    'cantidad'        => $detalle->cantidad_comprada,
                    'fecha'           => now(),
                    'referencia'      => 'Compra #' . $compra->id_compra,
                ]);
            }
        });

        return redirect()->route('almacen.compras.show', $id)
            ->with('success', 'Compra marcada como recibida. Inventario actualizado.');
    }
}
