<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    /**
     * Calcula el stock disponible de un producto en una talla específica,
     * basado ÚNICAMENTE en el lote actual (FIFO) para evitar cruces.
     */
    private function stockDisponible(int $idProducto, int $idTalla): int
    {
        return (int) DetalleCompra::where('id_producto', $idProducto)
            ->where('id_talla', $idTalla)
            ->where('cantidad_restante', '>', 0)
            ->orderBy('id_detalle_compra', 'asc')
            ->value('cantidad_restante') ?? 0;
    }

    /**
     * Mostrar el carrito.
     */
    public function index()
    {
        $carrito = session('carrito', []);

        // Actualizar precios dinámicos y restringir al stock del lote actual
        $cambios = false;
        foreach ($carrito as $clave => &$item) {
            $producto = Producto::find($item['id_producto']);
            if (!$producto) {
                unset($carrito[$clave]);
                $cambios = true;
                continue;
            }

            // Actualizar precio a la cotización actual
            if ($item['precio'] != $producto->precio_calculado) {
                $item['precio'] = $producto->precio_calculado;
                $cambios = true;
            }

            // Validar que la cantidad no sobrepase al Lote Actual
            $stockActual = $this->stockDisponible($item['id_producto'], $item['id_talla']);
            if ($item['cantidad'] > $stockActual) {
                $cambios = true;
                if ($stockActual == 0) {
                    unset($carrito[$clave]);
                } else {
                    $item['cantidad'] = $stockActual;
                }
            }
        }

        if ($cambios) {
            session(['carrito' => $carrito]);
        }

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
            $carrito[$clave] = [
                'id_producto' => $producto->id_producto,
                'id_talla'    => $talla->id_talla,
                'nombre'      => $producto->nombre,
                'talla'       => $talla->nombre,
                'precio'      => $producto->precio_calculado,
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

