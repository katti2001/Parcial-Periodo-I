<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Equipo;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['categoria', 'equipo'])
            ->orderBy('nombre')
            ->paginate(15);

        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $equipos    = Equipo::orderBy('nombre')->get();
        return view('admin.productos.create', compact('categorias', 'equipos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku_base'          => 'required|string|max:100|unique:productos',
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'precio_venta_base' => 'required|numeric|min:0',
            'id_categoria'      => 'nullable|integer|exists:categorias,id_categoria',
            'id_equipo'         => 'nullable|integer|exists:equipos,id_equipo',
            'activo'            => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        Producto::create($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit($id)
    {
        $producto   = Producto::findOrFail($id);
        $categorias = Categoria::orderBy('nombre')->get();
        $equipos    = Equipo::orderBy('nombre')->get();
        return view('admin.productos.edit', compact('producto', 'categorias', 'equipos'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'sku_base'          => 'required|string|max:100|unique:productos,sku_base,' . $id . ',id_producto',
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'precio_venta_base' => 'required|numeric|min:0',
            'id_categoria'      => 'nullable|integer|exists:categorias,id_categoria',
            'id_equipo'         => 'nullable|integer|exists:equipos,id_equipo',
            'activo'            => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo');
        $producto->update($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => false]);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto desactivado.');
    }
}
