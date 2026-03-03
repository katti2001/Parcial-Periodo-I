<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\DetalleCompra;
use App\Models\Equipo;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
            ->where('activo', true);

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->filled('equipo')) {
            $query->where('id_equipo', $request->equipo);
        }

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->buscar . '%');
            });
        }

        $todos = $query->orderBy('nombre')->orderBy('id_producto')->get();

        $productosUnicos = $todos->groupBy('nombre')->map(fn($grupo) => $grupo->first());

        $perPage  = 12;
        $page     = $request->input('page', 1);
        $offset   = ($page - 1) * $perPage;
        $items    = $productosUnicos->slice($offset, $perPage)->values();

        $productos = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $productosUnicos->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categorias = Categoria::orderBy('nombre')->get();
        $equipos    = Equipo::orderBy('nombre')->get();

        return view('catalogo.index', compact('productos', 'categorias', 'equipos'));
    }

    public function show($id)
    {
        $producto = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
            ->where('activo', true)
            ->findOrFail($id);

        $idsGrupo = Producto::where('nombre', $producto->nombre)
            ->where('activo', true)
            ->pluck('id_producto');

        $tallas = Talla::orderBy('nombre')->get();

        $stockPorTalla = [];
        foreach ($tallas as $talla) {
            $stockActual = (int) DetalleCompra::whereIn('id_producto', $idsGrupo)
                ->where('id_talla', $talla->id_talla)
                ->where('cantidad_restante', '>', 0)
                ->orderBy('id_detalle_compra', 'asc')
                ->value('cantidad_restante') ?? 0;

            if ($stockActual > 0) {
                $stockPorTalla[$talla->id_talla] = $stockActual;
            }
        }

        return view('catalogo.show', compact('producto', 'tallas', 'stockPorTalla'));
    }
}
