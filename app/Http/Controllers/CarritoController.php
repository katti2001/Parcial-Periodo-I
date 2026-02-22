<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    /**
     * Calcula el stock disponible de un producto en una talla específica.
     * Stock = suma de cantidad_restante en detalle_compras.
     * Busca también en todos los productos con el mismo nombre (grupo de variantes).
     */
    private function stockDisponible(int $idProducto, int $idTalla): int
    {
        // Obtener todos los ids del grupo (mismo nombre)
        $nombre = Producto::where('id_producto', $idProducto)->value('nombre');
        $idsGrupo = Producto::where('nombre', $nombre)->where('activo', true)->pluck('id_producto');

        return (int) DetalleCompra::whereIn('id_producto', $idsGrupo)
            ->where('id_talla', $idTalla)
            ->sum('cantidad_restante');
    }

    /**
     * Obtiene el precio de venta vigente (FIFO) para un producto+talla.
     * Devuelve el precio_venta del lote más antiguo con stock restante.
     * Si no hay lotes con precio_venta guardado, usa precio_venta_base del producto.
     */
    private function precioVigente(int $idProducto, int $idTalla): float
    {
        $nombre = Producto::where('id_producto', $idProducto)->value('nombre');
        $idsGrupo = Producto::where('nombre', $nombre)->where('activo', true)->pluck('id_producto');

        $lote = DetalleCompra::whereIn('id_producto', $idsGrupo)
            ->where('id_talla', $idTalla)
            ->where('cantidad_restante', '>', 0)
            ->where('precio_venta', '>', 0)
            ->orderBy('id_detalle_compra', 'asc')
            ->select('precio_venta')
            ->first();

        if ($lote) {
            return (float) $lote->precio_venta;
        }

        // Fallback: precio_venta_base del producto
        return (float) Producto::where('id_producto', $idProducto)->value('precio_venta_base');
    }

    /**
     * Mostrar el carrito.
     */
    public function index()
    {
        $carrito = session('carrito', []);
        $total   = collect($carrito)->sum(fn($item) => $item['precio'] * $item['cantidad']);

        return view('carrito.index', compact('carrito', 'total'));
    }

    /**
     * Agregar producto al carrito.
     */
    public function agregar(Request $request, $id)
    {
        $request->validate([
            'id_talla'  => 'required|integer',
            'cantidad'  => 'required|integer|min:1|max:5',
        ]);

        $producto = Producto::with('imagenes_productos')->where('activo', true)->findOrFail($id);
        $talla    = Talla::findOrFail($request->id_talla);

        $carrito   = session('carrito', []);
        $clave     = $producto->id_producto . '_' . $talla->id_talla;
        $cantidadSolicitada = (int) $request->cantidad;
        $cantidadEnCarrito  = isset($carrito[$clave]) ? $carrito[$clave]['cantidad'] : 0;
        $cantidadTotal      = $cantidadEnCarrito + $cantidadSolicitada;

        // Verificar stock disponible
        $stock = $this->stockDisponible($producto->id_producto, $talla->id_talla);

        if ($stock <= 0) {
            return redirect()->back()
                ->with('error', 'No hay stock disponible para la talla ' . $talla->nombre . '.');
        }

        if ($cantidadTotal > $stock) {
            return redirect()->back()
                ->with('error', "La cantidad de camisas sobrepasa la disponible. Solo hay {$stock} unidad(es) en stock para la talla {$talla->nombre}.");
        }

        if (isset($carrito[$clave])) {
            $carrito[$clave]['cantidad'] = $cantidadTotal;
        } else {
            $precioActual = $this->precioVigente($producto->id_producto, $talla->id_talla);

            $carrito[$clave] = [
                'id_producto' => $producto->id_producto,
                'id_talla'    => $talla->id_talla,
                'nombre'      => $producto->nombre,
                'talla'       => $talla->nombre,
                'precio'      => $precioActual,
                'cantidad'    => $cantidadTotal,
                'imagen'      => optional($producto->imagenes_productos->first())->url_imagen,
            ];
        }

        session(['carrito' => $carrito]);

        return redirect()->route('carrito.index')
            ->with('success', 'Producto agregado al carrito.');
    }

    /**
     * Actualizar cantidad de un item.
     */
    public function actualizar(Request $request, $clave)
    {
        $request->validate(['cantidad' => 'required|integer|min:1|max:5']);

        $carrito = session('carrito', []);

        if (isset($carrito[$clave])) {
            $item       = $carrito[$clave];
            $nuevaCant  = (int) $request->cantidad;
            $stock      = $this->stockDisponible($item['id_producto'], $item['id_talla']);

            if ($nuevaCant > $stock) {
                return redirect()->route('carrito.index')
                    ->with('error', "Solo hay {$stock} unidad(es) disponibles en talla {$item['talla']}.");
            }

            $carrito[$clave]['cantidad'] = $nuevaCant;
            session(['carrito' => $carrito]);
        }

        return redirect()->route('carrito.index');
    }

    /**
     * Eliminar un item del carrito.
     */
    public function eliminar($clave)
    {
        $carrito = session('carrito', []);
        unset($carrito[$clave]);
        session(['carrito' => $carrito]);

        return redirect()->route('carrito.index')
            ->with('success', 'Producto eliminado del carrito.');
    }

    /**
     * Vaciar el carrito completo.
     */
    public function vaciar()
    {
        session()->forget('carrito');
        return redirect()->route('carrito.index')
            ->with('success', 'Carrito vaciado.');
    }
}

