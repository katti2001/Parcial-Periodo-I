<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
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
            'cantidad'  => 'required|integer|min:1|max:10',
        ]);

        $producto = Producto::with('imagenes_productos')->where('activo', true)->findOrFail($id);
        $talla    = Talla::findOrFail($request->id_talla);

        $carrito = session('carrito', []);
        $clave   = $producto->id_producto . '_' . $talla->id_talla;

        if (isset($carrito[$clave])) {
            $carrito[$clave]['cantidad'] += (int) $request->cantidad;
        } else {
            $carrito[$clave] = [
                'id_producto' => $producto->id_producto,
                'id_talla'    => $talla->id_talla,
                'nombre'      => $producto->nombre,
                'talla'       => $talla->nombre,
                'precio'      => $producto->precio_venta_base,
                'cantidad'    => (int) $request->cantidad,
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
        $request->validate(['cantidad' => 'required|integer|min:1|max:10']);

        $carrito = session('carrito', []);

        if (isset($carrito[$clave])) {
            $carrito[$clave]['cantidad'] = (int) $request->cantidad;
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
