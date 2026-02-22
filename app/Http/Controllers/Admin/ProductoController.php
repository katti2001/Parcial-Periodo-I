<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Equipo;
use App\Models\ImagenesProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
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
            'imagenes'          => 'nullable|array|max:5',
            'imagenes.*'        => 'image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        unset($data['imagenes']);

        $producto = Producto::create($data);

        // Subir imágenes a Cloudinary
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $i => $archivo) {
                $resultado = Cloudinary::upload($archivo->getRealPath(), [
                    'folder' => 'tienda_paypal/productos',
                ]);

                ImagenesProducto::create([
                    'id_producto'  => $producto->id_producto,
                    'url_imagen'   => $resultado->getSecurePath(),
                    'es_principal' => $i === 0, // la primera imagen es la principal
                ]);
            }
        }

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit($id)
    {
        $producto   = Producto::with('imagenes_productos')->findOrFail($id);
        $categorias = Categoria::orderBy('nombre')->get();
        $equipos    = Equipo::orderBy('nombre')->get();
        return view('admin.productos.edit', compact('producto', 'categorias', 'equipos'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'sku_base'              => 'required|string|max:100|unique:productos,sku_base,' . $id . ',id_producto',
            'nombre'                => 'required|string|max:255',
            'descripcion'           => 'nullable|string',
            'precio_venta_base'     => 'required|numeric|min:0',
            'id_categoria'          => 'nullable|integer|exists:categorias,id_categoria',
            'id_equipo'             => 'nullable|integer|exists:equipos,id_equipo',
            'activo'                => 'boolean',
            'imagenes'              => 'nullable|array|max:5',
            'imagenes.*'            => 'image|mimes:jpg,jpeg,png,webp|max:3072',
            'eliminar_imagenes'     => 'nullable|array',
            'eliminar_imagenes.*'   => 'integer|exists:imagenes_productos,id_imagen',
        ]);

        $data['activo'] = $request->boolean('activo');
        unset($data['imagenes'], $data['eliminar_imagenes']);

        $producto->update($data);

        // Eliminar imágenes seleccionadas en Cloudinary y en BD
        if ($request->filled('eliminar_imagenes')) {
            $aEliminar = ImagenesProducto::whereIn('id_imagen', $request->eliminar_imagenes)
                ->where('id_producto', $producto->id_producto)
                ->get();

            foreach ($aEliminar as $img) {
                $publicId = $this->extraerPublicId($img->url_imagen);
                if ($publicId) {
                    Cloudinary::destroy($publicId);
                }
                $img->delete();
            }
        }

        // Subir nuevas imágenes a Cloudinary
        if ($request->hasFile('imagenes')) {
            $tieneImagenes = $producto->imagenes_productos()->count();

            foreach ($request->file('imagenes') as $i => $archivo) {
                $resultado = Cloudinary::upload($archivo->getRealPath(), [
                    'folder' => 'tienda_paypal/productos',
                ]);

                ImagenesProducto::create([
                    'id_producto'  => $producto->id_producto,
                    'url_imagen'   => $resultado->getSecurePath(),
                    'es_principal' => ($tieneImagenes === 0 && $i === 0),
                ]);
            }
        }

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

    // ─── Helper ──────────────────────────────────────────────────────────────

    /**
     * Extrae el public_id de Cloudinary a partir de una URL segura.
     * Ejemplo: https://res.cloudinary.com/demo/image/upload/v123/tienda_paypal/productos/abc.jpg
     * → tienda_paypal/productos/abc
     */
    private function extraerPublicId(string $url): ?string
    {
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+)\.[a-z]+$/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }
}
