<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\DetalleCompra;
use App\Models\DetallePedido;
use App\Models\DetalleFactura;
use App\Models\Kardex;
use App\Models\Factura;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Exceptions\ErrorException;

class CheckoutController extends Controller
{
    private function paypalClient()
    {
        return PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    config('services.paypal.client_id'),
                    config('services.paypal.client_secret')
                )
            )
            ->environment(
                config('services.paypal.mode', 'sandbox') === 'live'
                    ? Environment::PRODUCTION
                    : Environment::SANDBOX
            )
            ->build();
    }

    public function index(Request $request)
    {
        $carrito = session('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        $cambios = false;
        foreach ($carrito as $clave => &$item) {
            $producto = \App\Models\Producto::find($item['id_producto']);
            if (!$producto) {
                unset($carrito[$clave]);
                $cambios = true;
                continue;
            }

            if ($item['precio'] != $producto->precio_calculado) {
                $item['precio'] = $producto->precio_calculado;
                $cambios = true;
            }

            $stockActual = (int) \App\Models\DetalleCompra::where('id_producto', $item['id_producto'])
                ->where('id_talla', $item['id_talla'])
                ->where('cantidad_restante', '>', 0)
                ->orderBy('id_detalle_compra', 'asc')
                ->value('cantidad_restante') ?? 0;

            if ($item['cantidad'] > $stockActual) {
                $cambios = true;
                if ($stockActual == 0) {
                    unset($carrito[$clave]);
                } else {
                    $item['cantidad'] = $stockActual;
                }
            }
        }

        if ($cambios) {
            session(['carrito' => $carrito]);
            return redirect()->route('carrito.index')
                ->with('error', 'Algunos productos en tu carrito han cambiado de precio o disponibilidad por actualización de inventario.');
        }

        $subtotal        = collect($carrito)->sum(fn($i) => $i['precio'] * $i['cantidad']);
        $monto_descuento = 0.0;
        $cupon           = null;

        if (session('cupon_id')) {
            $cupon = Cupon::where('activo', true)->find(session('cupon_id'));
            if ($cupon) {
                $monto_descuento = $cupon->tipo_descuento === 'porcentaje'
                    ? round($subtotal * ($cupon->valor / 100), 2)
                    : min($cupon->valor, $subtotal);
            }
        }

        $total = max(0, $subtotal - $monto_descuento);

        return view('checkout.index', compact(
            'carrito', 'subtotal',
            'monto_descuento', 'cupon', 'total'
        ));
    }

    public function aplicarCupon(Request $request)
    {
        $request->validate(['codigo' => 'required|string']);

        $cupon = Cupon::where('codigo', $request->codigo)
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                  ->orWhere('fecha_expiracion', '>=', now());
            })
            ->first();

        if (!$cupon) {
            return back()->with('error', 'Cupón inválido o expirado.');
        }

        session(['cupon_id' => $cupon->id_cupon]);
        return back()->with('success', 'Cupón aplicado: ' . $cupon->codigo);
    }

    public function crearOrden(Request $request)
    {
        $carrito = session('carrito', []);

        if (empty($carrito)) {
            return response()->json(['error' => 'Carrito vacío'], 400);
        }

        $cambios = false;
        foreach ($carrito as $clave => &$item) {
            $producto = \App\Models\Producto::find($item['id_producto']);
            if (!$producto || $item['precio'] != $producto->precio_calculado) {
                $cambios = true;
                break;
            }
            $stockActual = (int) \App\Models\DetalleCompra::where('id_producto', $item['id_producto'])
                ->where('id_talla', $item['id_talla'])
                ->where('cantidad_restante', '>', 0)
                ->orderBy('id_detalle_compra', 'asc')
                ->value('cantidad_restante') ?? 0;
            if ($item['cantidad'] > $stockActual) {
                $cambios = true;
                break;
            }
        }

        if ($cambios) {
            return response()->json(['error' => 'RELOAD_CART'], 409);
        }

        $subtotal        = collect($carrito)->sum(fn($i) => $i['precio'] * $i['cantidad']);
        $monto_descuento = 0.0;

        if (session('cupon_id')) {
            $cupon = Cupon::where('activo', true)->find(session('cupon_id'));
            if ($cupon) {
                $monto_descuento = $cupon->tipo_descuento === 'porcentaje'
                    ? round($subtotal * ($cupon->valor / 100), 2)
                    : min($cupon->valor, $subtotal);
            }
        }

        $total = number_format(max(0, $subtotal - $monto_descuento), 2, '.', '');

        try {
            $client = $this->paypalClient();

            $orderRequest = OrderRequestBuilder::init(
                CheckoutPaymentIntent::CAPTURE,
                [
                    PurchaseUnitRequestBuilder::init(
                        AmountWithBreakdownBuilder::init('USD', $total)->build()
                    )->build()
                ]
            )->build();

            $response = $client->getOrdersController()->createOrder([
                'body' => $orderRequest,
            ]);

            return response()->json(['id' => $response->getResult()->getId()]);
        } catch (\Exception $e) {
            Log::error('PayPal crearOrden error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al crear orden PayPal'], 500);
        }
    }

    public function capturarOrden(Request $request, $orderID)
    {
        try {
            $client   = $this->paypalClient();
            $response = $client->getOrdersController()->captureOrder([
                'id' => $orderID,
            ]);

            $result  = $response->getResult();
            $payerId = $result->getPayer()?->getPayerId() ?? '';

            $carrito         = session('carrito', []);
            $subtotal        = collect($carrito)->sum(fn($i) => $i['precio'] * $i['cantidad']);
            $monto_descuento = 0.0;
            $cupon_id        = null;

            if (session('cupon_id')) {
                $cupon = Cupon::where('activo', true)->find(session('cupon_id'));
                if ($cupon) {
                    $cupon_id        = $cupon->id_cupon;
                    $monto_descuento = $cupon->tipo_descuento === 'porcentaje'
                        ? round($subtotal * ($cupon->valor / 100), 2)
                        : min($cupon->valor, $subtotal);
                }
            }

            $total = max(0, $subtotal - $monto_descuento);

            $pedidoId = DB::transaction(function () use (
                $carrito, $subtotal, $monto_descuento,
                $cupon_id, $total, $orderID, $payerId
            ) {
                $pedido = Pedido::create([
                    'id_usuario'      => Auth::id(),
                    'id_cupon'        => $cupon_id,
                    'subtotal'        => $subtotal,
                    'monto_descuento' => $monto_descuento,
                    'costo_envio'     => 0,
                    'total'           => $total,
                    'moneda'          => 'USD',
                    'estado_pago'     => 'pagado',
                    'paypal_order_id' => $orderID,
                    'paypal_payer_id' => $payerId,
                    'estado_pedido'   => 'procesando',
                    'fecha_pedido'    => now(),
                ]);

                foreach ($carrito as $item) {
                    DetallePedido::create([
                        'id_pedido'             => $pedido->id_pedido,
                        'id_producto'           => $item['id_producto'],
                        'id_talla'              => $item['id_talla'],
                        'cantidad'              => $item['cantidad'],
                        'precio_unitario'       => $item['precio'],
                    ]);

                    $porDescontar = $item['cantidad'];

                    $lotes = DetalleCompra::where('id_producto', $item['id_producto'])
                        ->where('id_talla', $item['id_talla'])
                        ->where('cantidad_restante', '>', 0)
                        ->orderBy('id_detalle_compra')
                        ->lockForUpdate()
                        ->get();

                    foreach ($lotes as $lote) {
                        if ($porDescontar <= 0) break;

                        $descuento = min($lote->cantidad_restante, $porDescontar);
                        $lote->decrement('cantidad_restante', $descuento);
                        $porDescontar -= $descuento;
                    }

                    Kardex::create([
                        'id_producto'     => $item['id_producto'],
                        'id_talla'        => $item['id_talla'],
                        'tipo_movimiento' => 'venta',
                        'cantidad'        => $item['cantidad'],
                        'fecha'           => now(),
                        'referencia'      => 'Pedido #' . $pedido->id_pedido,
                    ]);
                }

                $factura = Factura::create([
                    'id_pedido'        => $pedido->id_pedido,
                    'id_usuario'       => Auth::id(),
                    'numero'           => 'FAC-' . now()->format('Ym') . '-' . $pedido->id_pedido,
                    'estado'           => 'emitida',
                    'fecha_emision'    => now(),
                    'fecha_vencimiento'=> now()->addDays(15),
                    'moneda'           => 'USD',
                    'subtotal'         => $subtotal,
                    'descuento'        => $monto_descuento,
                    'impuesto'         => 0,
                    'costo_envio'      => 0,
                    'total'            => $total,
                    'notas'            => null,
                ]);

                foreach ($carrito as $item) {
                    DetalleFactura::create([
                        'id_factura'      => $factura->id_factura,
                        'id_producto'     => $item['id_producto'],
                        'id_talla'        => $item['id_talla'],
                        'cantidad'        => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                    ]);
                }

                return $pedido->id_pedido;
            });

            session()->forget(['carrito', 'cupon_id']);

            return response()->json([
                'success'   => true,
                'pedido_id' => $pedidoId,
            ]);
        } catch (ErrorException $e) {
            Log::error('PayPal capturarOrden — API error', [
                'name'     => $e->getName(),
                'message'  => $e->getMessageProperty(),
                'debug_id' => $e->getDebugId(),
                'details'  => $e->getDetails(),
                'http_status' => $e->hasResponse() ? $e->getHttpResponse()->getStatusCode() : null,
            ]);
            return response()->json([
                'error' => 'Error de PayPal: ' . ($e->getMessageProperty() ?: $e->getName()),
            ], 500);
        } catch (\Exception $e) {
            Log::error('PayPal capturarOrden error', [
                'message'  => $e->getMessage(),
                'class'    => get_class($e),
                'file'     => $e->getFile() . ':' . $e->getLine(),
                'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                'trace'    => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Error al capturar pago: ' . $e->getMessage()], 500);
        }
    }

    public function confirmacion($id)
    {
        $pedido = Pedido::with(['detalle_pedidos.producto', 'detalle_pedidos.talla'])
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        $factura = Factura::where('id_pedido', $pedido->id_pedido)->first();

        return view('checkout.confirmacion', compact('pedido', 'factura'));
    }
}
