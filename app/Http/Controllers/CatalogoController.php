<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Equipo;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Listado público de productos con filtros.
     */
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
            ->where('activo', true);

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        // Filtro por equipo
        if ($request->filled('equipo')) {
            $query->where('id_equipo', $request->equipo);
        }

        // Filtro por búsqueda de texto
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->buscar . '%');
            });
        }

        $productos   = $query->orderBy('nombre')->paginate(12)->withQueryString();
        $categorias  = Categoria::orderBy('nombre')->get();
        $equipos     = Equipo::orderBy('nombre')->get();

        return view('catalogo.index', compact('productos', 'categorias', 'equipos'));
    }

    /**
     * Detalle de un producto.
     */
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
            ->where('activo', true)
            ->findOrFail($id);

        $tallas = Talla::orderBy('nombre')->get();

        return view('catalogo.show', compact('producto', 'tallas'));
    }
}
