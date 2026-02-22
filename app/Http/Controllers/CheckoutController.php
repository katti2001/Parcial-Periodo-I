<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;

class CheckoutController extends Controller
{
    private function paypalClient()
    {
        return PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    env('PAYPAL_CLIENT_ID'),
                    env('PAYPAL_CLIENT_SECRET')
                )
            )
            ->environment(
                env('PAYPAL_MODE', 'sandbox') === 'live'
                    ? Environment::PRODUCTION
                    : Environment::SANDBOX
            )
            ->build();
    }

    /**
     * Mostrar resumen del checkout con opción de cupón.
     */
    public function index(Request $request)
    {
        $carrito = session('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        $subtotal        = collect($carrito)->sum(fn($i) => $i['precio'] * $i['cantidad']);
        $costo_envio     = 5.00;
        $monto_descuento = 0.0;
        $cupon           = null;

        // Aplicar cupón si existe en sesión
        if (session('cupon_id')) {
            $cupon = Cupon::where('activo', true)->find(session('cupon_id'));
            if ($cupon) {
                $monto_descuento = $cupon->tipo_descuento === 'porcentaje'
                    ? round($subtotal * ($cupon->valor / 100), 2)
                    : min($cupon->valor, $subtotal);
            }
        }

        $total = max(0, $subtotal - $monto_descuento + $costo_envio);

        return view('checkout.index', compact(
            'carrito', 'subtotal', 'costo_envio',
            'monto_descuento', 'cupon', 'total'
        ));
    }

    /**
     * Aplicar cupón de descuento.
     */
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

    /**
     * Crear orden en PayPal y devolver el orderID al frontend.
     */
    public function crearOrden(Request $request)
    {
        $carrito = session('carrito', []);

        if (empty($carrito)) {
            return response()->json(['error' => 'Carrito vacío'], 400);
        }

        $subtotal        = collect($carrito)->sum(fn($i) => $i['precio'] * $i['cantidad']);
        $costo_envio     = 5.00;
        $monto_descuento = 0.0;

        if (session('cupon_id')) {
            $cupon = Cupon::where('activo', true)->find(session('cupon_id'));
            if ($cupon) {
                $monto_descuento = $cupon->tipo_descuento === 'porcentaje'
                    ? round($subtotal * ($cupon->valor / 100), 2)
                    : min($cupon->valor, $subtotal);
            }
        }

        $total = number_format(max(0, $subtotal - $monto_descuento + $costo_envio), 2, '.', '');

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

            $response = $client->getOrdersController()->ordersCreate([
                'body' => $orderRequest,
            ]);

            return response()->json(['id' => $response->getResult()->getId()]);
        } catch (\Exception $e) {
            Log::error('PayPal crearOrden error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al crear orden PayPal'], 500);
        }
    }

    /**
     * Capturar el pago luego de aprobación en PayPal.
     */
    public function capturarOrden(Request $request, $orderID)
    {
        try {
            $client   = $this->paypalClient();
            $response = $client->getOrdersController()->ordersCapture([
                'id' => $orderID,
            ]);

            $result  = $response->getResult();
            $payerId = $result->getPayer()?->getPayerId() ?? '';

            // Calcular totales
            $carrito         = session('carrito', []);
            $subtotal        = collect($carrito)->sum(fn($i) => $i['precio'] * $i['cantidad']);
            $costo_envio     = 5.00;
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

            $total = max(0, $subtotal - $monto_descuento + $costo_envio);

            // Crear pedido en BD
            $pedido = Pedido::create([
                'id_usuario'      => Auth::id(),
                'id_cupon'        => $cupon_id,
                'subtotal'        => $subtotal,
                'monto_descuento' => $monto_descuento,
                'costo_envio'     => $costo_envio,
                'total'           => $total,
                'moneda'          => 'USD',
                'estado_pago'     => 'pagado',
                'paypal_order_id' => $orderID,
                'paypal_payer_id' => $payerId,
                'estado_pedido'   => 'pendiente',
                'fecha_pedido'    => now(),
            ]);

            // Crear detalles
            foreach ($carrito as $item) {
                DetallePedido::create([
                    'id_pedido'             => $pedido->id_pedido,
                    'id_producto'           => $item['id_producto'],
                    'id_talla'              => $item['id_talla'],
                    'cantidad'              => $item['cantidad'],
                    'precio_venta_unitario' => $item['precio'],
                ]);
            }

            // Limpiar sesión
            session()->forget(['carrito', 'cupon_id']);

            return response()->json([
                'success'   => true,
                'pedido_id' => $pedido->id_pedido,
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal capturarOrden error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al capturar pago'], 500);
        }
    }

    /**
     * Página de confirmación de pedido exitoso.
     */
    public function confirmacion($id)
    {
        $pedido = Pedido::with(['detalle_pedidos.producto', 'detalle_pedidos.talla'])
            ->where('id_usuario', Auth::id())
            ->findOrFail($id);

        return view('checkout.confirmacion', compact('pedido'));
    }
}
