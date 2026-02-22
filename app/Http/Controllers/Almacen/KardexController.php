<?php

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Models\Kardex;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Http\Request;

class KardexController extends Controller
{
    public function index(Request $request)
    {
        $query = Kardex::with(['producto', 'talla'])
            ->orderByDesc('fecha')
            ->orderByDesc('id_movimiento');

        if ($request->filled('id_producto')) {
            $query->where('id_producto', $request->id_producto);
        }

        if ($request->filled('id_talla')) {
            $query->where('id_talla', $request->id_talla);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_movimiento', $request->tipo);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $movimientos = $query->paginate(20)->withQueryString();
        $productos   = Producto::orderBy('nombre')->get();
        $tallas      = Talla::orderBy('nombre')->get();

        return view('almacen.kardex.index', compact('movimientos', 'productos', 'tallas'));
    }
}
