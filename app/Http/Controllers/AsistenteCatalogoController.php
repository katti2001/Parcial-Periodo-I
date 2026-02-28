<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Talla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenteCatalogoController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string|max:500',
        ]);

        $mensaje = trim($request->input('mensaje'));

        // ── 1. Cargar productos activos con categoría, equipo e imágenes ──────
        $productos = Producto::with(['categoria', 'equipo', 'imagenes_productos'])
            ->where('activo', true)
            ->get();

        // ── 2. Stock disponible por producto ──────────────────────────────────
        $stockMap = DetalleCompra::select('dc.id_producto', DB::raw('SUM(dc.cantidad_restante) as total'))
            ->from('detalle_compras as dc')
            ->join('compras as c', 'c.id_compra', '=', 'dc.id_compra')
            ->where('c.estado', 'recibido')
            ->groupBy('dc.id_producto')
            ->pluck('total', 'dc.id_producto');

        // ── 3. Tallas disponibles por producto (con id numérico) ──────────────
        $tallasMap = DetalleCompra::select(
                'dc.id_producto',
                'dc.id_talla',
                DB::raw('SUM(dc.cantidad_restante) as stock_talla')
            )
            ->from('detalle_compras as dc')
            ->join('compras as c', 'c.id_compra', '=', 'dc.id_compra')
            ->where('c.estado', 'recibido')
            ->groupBy('dc.id_producto', 'dc.id_talla')
            ->having('stock_talla', '>', 0)
            ->get()
            ->groupBy('id_producto');

        $todasTallas = Talla::pluck('nombre', 'id_talla');

        // ── 4. Construir catálogo para el prompt ──────────────────────────────
        $catalogo = $productos->map(function ($p) use ($stockMap, $tallasMap, $todasTallas) {
            $stockTotal = (int) ($stockMap[$p->id_producto] ?? 0);

            // Tallas como objetos {id, nombre} para que Gemini pueda usar el id
            $tallasDisp = collect($tallasMap[$p->id_producto] ?? [])
                ->map(fn($t) => [
                    'id'     => (int) $t->id_talla,
                    'nombre' => $todasTallas[$t->id_talla] ?? '?',
                ])
                ->values()
                ->toArray();

            return [
                'id'        => $p->id_producto,
                'nombre'    => $p->nombre,
                'categoria' => $p->categoria?->nombre ?? 'Sin categoría',
                'equipo'    => $p->equipo?->nombre    ?? 'Sin equipo',
                'precio'    => $p->precio_venta_base,
                'stock'     => $stockTotal,
                'tallas'    => $tallasDisp,
                'url'       => route('catalogo.show', $p->id_producto),
            ];
        })->values()->toArray();

        $catalogoJson = json_encode($catalogo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // ── 5. Prompt para Gemini ─────────────────────────────────────────────
        $systemPrompt = <<<PROMPT
Eres un asistente de compras de una tienda deportiva en línea.
Ayudas a los clientes a encontrar productos, verificar stock y agregar al carrito.

Catálogo actual (cada talla tiene "id" numérico y "nombre"):
{$catalogoJson}

Reglas:
1. Cuando el cliente pida un producto, búscalo de forma flexible (por nombre, categoría, equipo).
2. Muestra precio, tallas disponibles y si hay o no stock.
3. Si el cliente pide agregar al carrito:
   - Si NO especificó talla, pregúntale qué talla desea (lista las disponibles).
   - Si SÍ especificó talla o hay una sola talla disponible, incluye la acción de carrito.
4. Sugiere siempre 1 o 2 productos similares (misma categoría o equipo).
5. Si no hay stock, dilo claramente y ofrece las alternativas.
6. Responde en español, de forma breve, amigable y directa.
7. Cuando puedas agregar al carrito (talla definida), incluye AL FINAL EXACTAMENTE este bloque (sin markdown, en una sola línea):

ACCION_JSON:{"accion":"agregar_carrito","id_producto":<id>,"id_talla":<id_talla>,"nombre":"<nombre>","talla":"<nombre_talla>","cantidad":1,"url":"<url>","similares":[{"id":<id>,"nombre":"<nombre>","precio":<precio>,"stock":<stock>,"url":"<url>"}]}

Si no puedes agregar (falta talla, sin stock, etc.), NO incluyas el bloque ACCION_JSON.
PROMPT;

        // ── 6. Llamar a Gemini via cURL nativo ───────────────────────────────
        $apiKey  = config('services.gemini.key');
        $url     = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$apiKey}";
        $payload = json_encode([
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $systemPrompt . "\n\nCliente dice: " . $mensaje]],
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
            return response()->json(['error' => true, 'mensaje' => 'Error al conectar con el asistente. Intenta de nuevo.'], 500);
        }

        $data  = json_decode($raw, true);
        $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // ── 7. Extraer acción JSON ─────────────────────────────────────────────
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
