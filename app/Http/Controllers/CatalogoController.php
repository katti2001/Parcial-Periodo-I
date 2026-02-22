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
    /**
     * Listado público de productos con filtros.
     * Agrupa por nombre para no mostrar duplicados (misma prenda, distintas tallas).
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

        // Traer todos los activos que coinciden, luego deduplicar por nombre
        // manteniendo el primero de cada grupo (el de menor id_producto)
        $todos = $query->orderBy('nombre')->orderBy('id_producto')->get();

        // Agrupar por nombre: quedarse con el representante de cada grupo
        $productosUnicos = $todos->groupBy('nombre')->map(fn($grupo) => $grupo->first());

        // Paginar manualmente sobre la colección deduplicada
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

    /**
     * Detalle de un producto.
     * Consolida el stock de TODOS los productos con el mismo nombre
     * para mostrar todas las tallas disponibles en una sola vista.
     */
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
            ->where('activo', true)
            ->findOrFail($id);

        // IDs de todos los productos con el mismo nombre (misma prenda, distintas entradas)
        $idsGrupo = Producto::where('nombre', $producto->nombre)
            ->where('activo', true)
            ->pluck('id_producto');

        $tallas = Talla::orderBy('nombre')->get();

        // Stock disponible por talla sumando todos los productos del grupo
        $stockPorTalla = DetalleCompra::whereIn('id_producto', $idsGrupo)
            ->where('cantidad_restante', '>', 0)
            ->selectRaw('id_talla, SUM(cantidad_restante) as total')
            ->groupBy('id_talla')
            ->pluck('total', 'id_talla');

        // Precio de venta vigente por talla: lote más antiguo con stock (FIFO)
        // Toma el precio_venta del primer detalle_compra con cantidad_restante > 0
        // ordenado por id_detalle_compra ASC (el más antiguo primero).
        $precioPorTalla = [];
        foreach ($tallas as $talla) {
            $loteVigente = DetalleCompra::whereIn('id_producto', $idsGrupo)
                ->where('id_talla', $talla->id_talla)
                ->where('cantidad_restante', '>', 0)
                ->orderBy('id_detalle_compra', 'asc')
                ->select('precio_venta')
                ->first();

            if ($loteVigente && $loteVigente->precio_venta > 0) {
                $precioPorTalla[$talla->id_talla] = (float) $loteVigente->precio_venta;
            }
        }

        // Precio base a mostrar: el del lote vigente más antiguo entre todas las tallas,
        // o bien el precio_venta_base del producto si no hay lotes aún.
        $precioMostrar = collect($precioPorTalla)->min() ?? $producto->precio_venta_base;

        return view('catalogo.show', compact(
            'producto', 'tallas', 'stockPorTalla', 'precioPorTalla', 'precioMostrar'
        ));
    }
}
