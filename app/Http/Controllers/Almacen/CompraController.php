<?php

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Equipo;
use App\Models\ImagenesProducto;
use App\Models\Kardex;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Talla;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor')
            ->orderByDesc('id_compra')
            ->paginate(15);

        return view('almacen.compras.index', compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre_empresa')->get();
        $productos   = Producto::where('activo', true)->orderBy('nombre')->get();
        $tallas      = Talla::orderBy('nombre')->get();
        $categorias  = Categoria::orderBy('nombre')->get();
        $equipos     = Equipo::orderBy('nombre')->get();

        return view('almacen.compras.create', compact('proveedores', 'productos', 'tallas', 'categorias', 'equipos'));
    }

    /**
     * Registrar una compra con sus detalles.
     * Crea productos nuevos si es necesario, calcula precio_venta_base con el margen indicado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_proveedor'              => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'              => 'required|date',
            'numero_factura_proveedor'  => 'nullable|string|max:50',
            'estado'                    => 'required|in:solicitado,recibido,cancelado',
            'margen'                    => 'required|numeric|min:0|max:500',
            'items'                     => 'required|array|min:1',
            'items.*.es_nuevo'          => 'required|in:0,1',
            'items.*.mismo_producto'    => 'nullable|in:0,1',
            // producto existente
            'items.*.id_producto'       => 'nullable|integer',
            // producto nuevo
            'items.*.sku_base'          => 'nullable|string|max:20',
            'items.*.nombre'            => 'nullable|string|max:100',
            'items.*.descripcion'       => 'nullable|string',
            'items.*.id_categoria'      => 'nullable|integer|exists:categorias,id_categoria',
            'items.*.id_equipo'         => 'nullable|integer|exists:equipos,id_equipo',
            // comunes
            'items.*.id_talla'          => 'required|exists:tallas,id_talla',
            'items.*.cantidad_comprada' => 'required|integer|min:1',
            'items.*.costo_unitario'    => 'required|numeric|min:0',
            // imágenes: máx 5 por slot, cada una imagen válida ≤ 5 MB
            'imagenes'                  => 'nullable|array',
            'imagenes.*'                => 'nullable|array|max:5',
            'imagenes.*.*'              => 'nullable|image|max:5120',
        ]);

        $margen = (float) $request->margen;

        // Validar SKUs de productos nuevos (solo filas líderes, mismo_producto=0) antes de la transacción
        $erroresSku      = [];
        $skusEnEstaCompra = [];
        foreach ($request->items as $i => $item) {
            if ((int) $item['es_nuevo'] !== 1) continue;
            if ((int) ($item['mismo_producto'] ?? 0) === 1) continue; // las filas "mismo" no tienen SKU propio

            $sku = trim($item['sku_base'] ?? '');
            if (empty($sku)) continue;

            if (in_array($sku, $skusEnEstaCompra)) {
                $erroresSku["items.{$i}.sku_base"] = ["El SKU «{$sku}» está duplicado en esta compra."];
            }
            $skusEnEstaCompra[] = $sku;

            if (Producto::where('sku_base', $sku)->exists()) {
                $erroresSku["items.{$i}.sku_base"] = ["El SKU «{$sku}» ya existe. Selecciónalo desde 'Producto existente'."];
            }
        }

        if (!empty($erroresSku)) {
            return back()->withInput()->withErrors($erroresSku);
        }

        // Subir imágenes a Cloudinary ANTES de la transacción para no mezclar IO con DB
        // imagenes[idx][] → array indexado por el mismo índice que items[idx]
        $imagenesSubidas = []; // idx => [ ['url' => ..., 'es_principal' => bool], ... ]
        $imagenesInput   = $request->file('imagenes', []);
        foreach ($imagenesInput as $idx => $archivos) {
            if (empty($archivos)) continue;
            $imagenesSubidas[$idx] = [];
            foreach (array_slice($archivos, 0, 5) as $pos => $archivo) {
                $resultado = Cloudinary::upload($archivo->getRealPath(), [
                    'folder' => 'tienda_paypal/productos',
                ]);
                $imagenesSubidas[$idx][] = [
                    'url'          => $resultado->getSecurePath(),
                    'es_principal' => ($pos === 0),
                ];
            }
        }

        DB::transaction(function () use ($request, $margen, $imagenesSubidas) {
            $total = 0;

            // Pre-procesar ítems: resolver o crear productos
            // $ultimoIdNuevo guarda el último id_producto creado para resolver filas "mismo_producto"
            $ultimoIdNuevo = null;
            $itemsResueltos = [];

            foreach ($request->items as $idx => $item) {
                $cantidad = (int)   $item['cantidad_comprada'];
                $costo    = (float) $item['costo_unitario'];
                $total   += $cantidad * $costo;

                $esNuevo   = (int) $item['es_nuevo'] === 1;
                $esMismo   = (int) ($item['mismo_producto'] ?? 0) === 1;

                if ($esNuevo && !$esMismo) {
                    // Fila líder: crear producto nuevo
                    if (empty($item['sku_base']) || empty($item['nombre'])) {
                        abort(422, 'SKU y nombre son obligatorios para productos nuevos.');
                    }

                    $precioVenta = round($costo * (1 + $margen / 100), 2);

                    $producto = Producto::create([
                        'sku_base'          => $item['sku_base'],
                        'nombre'            => $item['nombre'],
                        'descripcion'       => $item['descripcion'] ?? null,
                        'precio_venta_base' => $precioVenta,
                        'id_categoria'      => $item['id_categoria'] ?: null,
                        'id_equipo'         => $item['id_equipo'] ?: null,
                        'activo'            => true,
                    ]);

                    $idProducto    = $producto->id_producto;
                    $ultimoIdNuevo = $idProducto;

                    // Asociar imágenes subidas para este índice
                    if (!empty($imagenesSubidas[$idx])) {
                        foreach ($imagenesSubidas[$idx] as $img) {
                            ImagenesProducto::create([
                                'id_producto'  => $idProducto,
                                'url_imagen'   => $img['url'],
                                'es_principal' => $img['es_principal'],
                            ]);
                        }
                    }

                } elseif ($esNuevo && $esMismo) {
                    // Fila "mismo producto": reutilizar el último producto nuevo creado
                    if ($ultimoIdNuevo === null) {
                        abort(422, 'No hay producto nuevo anterior para la fila "mismo producto".');
                    }
                    $idProducto = $ultimoIdNuevo;

                } else {
                    // Producto existente
                    $idProducto    = (int) $item['id_producto'];
                    $ultimoIdNuevo = null; // romper la cadena de "mismo producto"
                }

                $itemsResueltos[] = [
                    'id_producto'       => $idProducto,
                    'id_talla'          => $item['id_talla'],
                    'cantidad_comprada' => $cantidad,
                    'costo_unitario'    => $costo,
                ];
            }

            $compra = Compra::create([
                'id_proveedor'             => $request->id_proveedor,
                'fecha_compra'             => $request->fecha_compra,
                'total_compra'             => $total,
                'numero_factura_proveedor' => $request->numero_factura_proveedor,
                'estado'                   => $request->estado,
            ]);

            foreach ($itemsResueltos as $item) {
                DetalleCompra::create([
                    'id_compra'         => $compra->id_compra,
                    'id_producto'       => $item['id_producto'],
                    'id_talla'          => $item['id_talla'],
                    'cantidad_comprada' => $item['cantidad_comprada'],
                    'cantidad_restante' => $item['cantidad_comprada'],
                    'costo_unitario'    => $item['costo_unitario'],
                ]);

                if ($request->estado === 'recibido') {
                    Kardex::create([
                        'id_producto'     => $item['id_producto'],
                        'id_talla'        => $item['id_talla'],
                        'tipo_movimiento' => 'compra',
                        'cantidad'        => $item['cantidad_comprada'],
                        'fecha'           => now(),
                        'referencia'      => 'Compra #' . $compra->id_compra,
                    ]);
                }
            }
        });

        return redirect()->route('almacen.compras.index')
            ->with('success', 'Compra registrada correctamente.');
    }

    public function show($id)
    {
        $compra = Compra::with([
            'proveedor',
            'detalle_compras.producto',
            'detalle_compras.talla',
        ])->findOrFail($id);

        return view('almacen.compras.show', compact('compra'));
    }

    /**
     * Marcar una compra como "recibida" e insertar en kardex.
     */
    public function recibirCompra($id)
    {
        $compra = Compra::with('detalle_compras')->findOrFail($id);

        if ($compra->estado === 'recibido') {
            return redirect()->route('almacen.compras.show', $id)
                ->with('error', 'Esta compra ya fue marcada como recibida.');
        }

        DB::transaction(function () use ($compra) {
            $compra->update(['estado' => 'recibido']);

            foreach ($compra->detalle_compras as $detalle) {
                Kardex::create([
                    'id_producto'     => $detalle->id_producto,
                    'id_talla'        => $detalle->id_talla,
                    'tipo_movimiento' => 'compra',
                    'cantidad'        => $detalle->cantidad_comprada,
                    'fecha'           => now(),
                    'referencia'      => 'Compra #' . $compra->id_compra,
                ]);
            }
        });

        return redirect()->route('almacen.compras.show', $id)
            ->with('success', 'Compra marcada como recibida. Inventario actualizado.');
    }
}
