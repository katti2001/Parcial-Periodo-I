<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\DetalleCompra;
use App\Models\Equipo;
use App\Models\ImagenesProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        $equipos    = Equipo::orderBy('nombre')->get();

        $productos = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
            ->when($request->filled('search'), fn($q) =>
                $q->where(function ($q2) use ($request) {
                    $q2->where('nombre', 'like', '%' . $request->search . '%')
                       ->orWhere('sku_base', 'like', '%' . $request->search . '%');
                })
            )
            ->when($request->filled('id_categoria'), fn($q) =>
                $q->where('id_categoria', $request->id_categoria)
            )
            ->when($request->filled('id_equipo'), fn($q) =>
                $q->where('id_equipo', $request->id_equipo)
            )
            ->when($request->filled('activo'), fn($q) =>
                $q->where('activo', $request->activo)
            )
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        // Stock disponible: compras ya recibidas (cantidad_restante no consumida)
        $stockDisponible = DetalleCompra::select('dc.id_producto', DB::raw('SUM(dc.cantidad_restante) as total'))
            ->from('detalle_compras as dc')
            ->join('compras as c', 'c.id_compra', '=', 'dc.id_compra')
            ->where('c.estado', 'recibido')
            ->groupBy('dc.id_producto')
            ->pluck('total', 'dc.id_producto');

        // Stock en camino: compras solicitadas (aún no recibidas)
        $stockEnCamino = DetalleCompra::select('dc.id_producto', DB::raw('SUM(dc.cantidad_comprada) as total'))
            ->from('detalle_compras as dc')
            ->join('compras as c', 'c.id_compra', '=', 'dc.id_compra')
            ->where('c.estado', 'solicitado')
            ->groupBy('dc.id_producto')
            ->pluck('total', 'dc.id_producto');

        return view('admin.productos.index', compact('productos', 'stockDisponible', 'stockEnCamino', 'categorias', 'equipos'));
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
            'imagenes.*'        => 'image|max:3072',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        unset($data['imagenes']);

        $producto = Producto::create($data);

        // Subir imágenes a Cloudinary
        $warningsImagenes = [];
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $i => $archivo) {
                try {
                    $resultado = Cloudinary::upload($archivo->getRealPath(), [
                        'folder' => 'tienda_paypal/productos',
                    ]);

                    ImagenesProducto::create([
                        'id_producto'  => $producto->id_producto,
                        'url_imagen'   => $resultado->getSecurePath(),
                        'es_principal' => $i === 0,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Cloudinary upload error (store)', [
                        'archivo' => $archivo->getClientOriginalName(),
                        'error'   => $e->getMessage(),
                    ]);
                    $warningsImagenes[] = 'No se pudo subir "' . $archivo->getClientOriginalName() . '": ' . $e->getMessage();
                }
            }
        }

        $redirect = redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');

        if (!empty($warningsImagenes)) {
            $redirect->with('warning', 'El producto fue creado, pero algunas imágenes no se subieron: ' . implode(' | ', $warningsImagenes));
        }

        return $redirect;
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
            'imagenes.*'            => 'image|max:3072',
            'eliminar_imagenes'     => 'nullable|array',
            'eliminar_imagenes.*'   => 'integer|exists:imagenes_productos,id_imagen',
        ]);

        $data['activo'] = $request->boolean('activo');
        unset($data['imagenes'], $data['eliminar_imagenes']);

        $producto->update($data);

        // Eliminar imágenes seleccionadas en Cloudinary y en BD
        $warningsImagenes = [];
        if ($request->filled('eliminar_imagenes')) {
            $aEliminar = ImagenesProducto::whereIn('id_imagen', $request->eliminar_imagenes)
                ->where('id_producto', $producto->id_producto)
                ->get();

            foreach ($aEliminar as $img) {
                $publicId = $this->extraerPublicId($img->url_imagen);
                if ($publicId) {
                    try {
                        Cloudinary::destroy($publicId);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Cloudinary destroy error (update)', [
                            'public_id' => $publicId,
                            'error'     => $e->getMessage(),
                        ]);
                        // Se elimina de la BD aunque falle en Cloudinary
                    }
                }
                $img->delete();
            }
        }

        // Subir nuevas imágenes a Cloudinary
        if ($request->hasFile('imagenes')) {
            $tieneImagenes  = $producto->imagenes_productos()->count();
            $tienePrincipal = $producto->imagenes_productos()->where('es_principal', true)->exists();

            foreach ($request->file('imagenes') as $i => $archivo) {
                try {
                    $resultado = Cloudinary::upload($archivo->getRealPath(), [
                        'folder' => 'tienda_paypal/productos',
                    ]);

                    $esPrincipal = ($tieneImagenes === 0 && $i === 0) || (!$tienePrincipal && $i === 0);

                    ImagenesProducto::create([
                        'id_producto'  => $producto->id_producto,
                        'url_imagen'   => $resultado->getSecurePath(),
                        'es_principal' => $esPrincipal,
                    ]);

                    if ($esPrincipal) {
                        $tienePrincipal = true;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Cloudinary upload error (update)', [
                        'archivo' => $archivo->getClientOriginalName(),
                        'error'   => $e->getMessage(),
                    ]);
                    $warningsImagenes[] = 'No se pudo subir "' . $archivo->getClientOriginalName() . '": ' . $e->getMessage();
                }
            }
        }

        $redirect = redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');

        if (!empty($warningsImagenes)) {
            $redirect->with('warning', 'El producto fue actualizado, pero algunas imágenes no se subieron: ' . implode(' | ', $warningsImagenes));
        }

        return $redirect;
    }

    public function destroy($id)
    {
        $producto = Producto::with('imagenes_productos')->findOrFail($id);

        // Eliminar imágenes de Cloudinary y de la BD antes de desactivar
        foreach ($producto->imagenes_productos as $img) {
            $publicId = $this->extraerPublicId($img->url_imagen);
            if ($publicId) {
                Cloudinary::destroy($publicId);
            }
            $img->delete();
        }

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
