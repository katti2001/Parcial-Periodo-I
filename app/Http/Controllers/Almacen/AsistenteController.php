<?php

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenteController extends Controller
{
    /**
     * Recibe un mensaje del chat, consulta productos + stock,
     * y devuelve una respuesta de Gemini con acciones estructuradas.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string|max:500',
        ]);

        $mensaje = trim($request->input('mensaje'));

        // ── 1. Cargar todos los productos activos con categoría y equipo ─────
        $productos = Producto::with(['categoria', 'equipo'])
            ->where('activo', true)
            ->get();

        // ── 2. Calcular stock disponible por producto ─────────────────────────
        $stockMap = DetalleCompra::select('dc.id_producto', DB::raw('SUM(dc.cantidad_restante) as total'))
            ->from('detalle_compras as dc')
            ->join('compras as c', 'c.id_compra', '=', 'dc.id_compra')
            ->where('c.estado', 'recibido')
            ->groupBy('dc.id_producto')
            ->pluck('total', 'dc.id_producto');

        // ── 3. Construir catálogo como texto para el prompt ───────────────────
        $catalogo = $productos->map(function ($p) use ($stockMap) {
            $stock     = $stockMap[$p->id_producto] ?? 0;
            $categoria = $p->categoria?->nombre ?? 'Sin categoría';
            $equipo    = $p->equipo?->nombre    ?? 'Sin equipo';

            return [
                'id'        => $p->id_producto,
                'sku'       => $p->sku_base,
                'nombre'    => $p->nombre,
                'categoria' => $categoria,
                'equipo'    => $equipo,
                'precio'    => $p->precio_calculado,
                'stock'     => (int) $stock,
            ];
        })->values()->toArray();

        $catalogoJson = json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // ── 4. prompt para Gemini ───────────────────────────────────
        $systemPrompt = <<<PROMPT
Eres un asistente de compras para una tienda de ropa deportiva.
Tu trabajo es ayudar al encargado de almacén a registrar una nueva compra a proveedor.

Tienes acceso al catálogo de productos activos con su stock actual:
{$catalogoJson}

Reglas:
1. Cuando el usuario pida un producto, búscalo en el catálogo (búsqueda flexible por nombre, categoría o equipo).
2. Informa si hay stock disponible o no.
3. Si el usuario pide agregarlo al formulario de compra, responde con una acción estructurada.
4. Sugiere siempre 1 o 2 productos similares (misma categoría o equipo).
5. Responde siempre en español, de forma breve y amigable.
6. Al final de tu respuesta, si hay que agregar producto, incluye EXACTAMENTE este bloque JSON (sin markdown):

ACCION_JSON:{"accion":"agregar","id_producto":<id>,"nombre":"<nombre>","precio":<precio>,"similares":[{"id":<id>,"nombre":"<nombre>","stock":<stock>}]}

Si no hay acción de agregar, no incluyas el bloque ACCION_JSON.
Si el producto no existe en el catálogo, dilo claramente y sugiere alternativas.
PROMPT;

        // ── 5. Llamar a la API de Gemini via cURL nativo ──────────────────────
        $apiKey  = config('services.gemini.key');
        $url     = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$apiKey}";
        $payload = json_encode([
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $systemPrompt . "\n\nMensaje del usuario: " . $mensaje]],
            ]],
            'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 512],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode >= 500) {
            return response()->json([
                'error'   => true,
                'mensaje' => 'Error al conectar con Gemini. Intenta de nuevo.',
            ], 500);
        }

        $data  = json_decode($raw, true);
        $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // ── 6. Extraer acción JSON si existe ──────────────────────────────────
        $accion      = null;
        $textoLimpio = $texto;

        if (preg_match('/ACCION_JSON:(\{.+?\})\s*$/s', $texto, $m)) {
            $decoded = json_decode($m[1], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $accion = $decoded;
            }
            $textoLimpio = trim(preg_replace('/ACCION_JSON:\{.+?\}\s*$/s', '', $texto));
        }

        return response()->json([
            'error'   => false,
            'mensaje' => $textoLimpio,
            'accion'  => $accion,
        ]);
    }
}
