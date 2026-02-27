<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PedidosSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Pedido 1: entregado — María (id_usuario=3) ───────────────────────
        DB::table('pedidos')->insert([
            'id_usuario'     => 3,
            'id_cupon'       => null,
            'subtotal'       => 159.98,
            'monto_descuento'=> 0,
            'costo_envio'    => 5.00,
            'total'          => 164.98,
            'moneda'         => 'USD',
            'estado_pago'    => 'pagado',
            'paypal_order_id'=> 'PAYPAL-ORDER-00001',
            'paypal_payer_id'=> 'PAYER-00001',
            'estado_pedido'  => 'entregado',
            'fecha_pedido'   => '2025-12-01 10:00:00',
        ]);

        DB::table('detalle_pedidos')->insert([
            ['id_pedido' => 1, 'id_producto' => 1, 'id_talla' => 4, 'cantidad' => 1, 'precio_unitario' => 79.99],
            ['id_pedido' => 1, 'id_producto' => 8, 'id_talla' => 3, 'cantidad' => 1, 'precio_unitario' => 59.99],
            ['id_pedido' => 1, 'id_producto' => 8, 'id_talla' => 4, 'cantidad' => 1, 'precio_unitario' => 59.99], // para devolución
        ]);

        // Actualizar cantidad_restante: descontar ventas (FIFO sobre compra 1)
        DB::table('detalle_compras')->where('id_compra', 1)->where('id_producto', 1)->where('id_talla', 4)->decrement('cantidad_restante', 1);
        DB::table('detalle_compras')->where('id_compra', 1)->where('id_producto', 8)->where('id_talla', 3)->decrement('cantidad_restante', 1);
        DB::table('detalle_compras')->where('id_compra', 1)->where('id_producto', 8)->where('id_talla', 4)->decrement('cantidad_restante', 1);

        // Kardex ventas
        DB::table('kardex')->insert([
            ['id_producto' => 1, 'id_talla' => 4, 'tipo_movimiento' => 'venta', 'cantidad' => 1, 'fecha' => '2025-12-01 10:05:00', 'referencia' => 'Pedido #1'],
            ['id_producto' => 8, 'id_talla' => 3, 'tipo_movimiento' => 'venta', 'cantidad' => 1, 'fecha' => '2025-12-01 10:05:00', 'referencia' => 'Pedido #1'],
            ['id_producto' => 8, 'id_talla' => 4, 'tipo_movimiento' => 'venta', 'cantidad' => 1, 'fecha' => '2025-12-01 10:05:00', 'referencia' => 'Pedido #1'],
        ]);

        // ─── Pedido 2: enviado — Juan (id_usuario=4) con cupón 10% ────────────
        DB::table('pedidos')->insert([
            'id_usuario'     => 4,
            'id_cupon'       => 1, // BIENVENIDO10
            'subtotal'       => 84.99,
            'monto_descuento'=> 8.50,
            'costo_envio'    => 5.00,
            'total'          => 81.49,
            'moneda'         => 'USD',
            'estado_pago'    => 'pagado',
            'paypal_order_id'=> 'PAYPAL-ORDER-00002',
            'paypal_payer_id'=> 'PAYER-00002',
            'estado_pedido'  => 'enviado',
            'fecha_pedido'   => '2026-01-15 14:30:00',
        ]);

        DB::table('detalle_pedidos')->insert([
            ['id_pedido' => 2, 'id_producto' => 3, 'id_talla' => 3, 'cantidad' => 1, 'precio_unitario' => 84.99],
        ]);

        DB::table('detalle_compras')->where('id_compra', 1)->where('id_producto', 3)->where('id_talla', 3)->decrement('cantidad_restante', 1);

        DB::table('kardex')->insert([
            ['id_producto' => 3, 'id_talla' => 3, 'tipo_movimiento' => 'venta', 'cantidad' => 1, 'fecha' => '2026-01-15 14:35:00', 'referencia' => 'Pedido #2'],
        ]);

        // ─── Pedido 3: procesando — Ana (id_usuario=5) ────────────────────────
        DB::table('pedidos')->insert([
            'id_usuario'     => 5,
            'id_cupon'       => null,
            'subtotal'       => 169.98,
            'monto_descuento'=> 0,
            'costo_envio'    => 5.00,
            'total'          => 174.98,
            'moneda'         => 'USD',
            'estado_pago'    => 'pagado',
            'paypal_order_id'=> 'PAYPAL-ORDER-00003',
            'paypal_payer_id'=> 'PAYER-00003',
            'estado_pedido'  => 'procesando',
            'fecha_pedido'   => '2026-02-10 09:15:00',
        ]);

        DB::table('detalle_pedidos')->insert([
            ['id_pedido' => 3, 'id_producto' => 5, 'id_talla' => 4, 'cantidad' => 1, 'precio_unitario' => 89.99],
            ['id_pedido' => 3, 'id_producto' => 6, 'id_talla' => 4, 'cantidad' => 1, 'precio_unitario' => 82.99],
        ]);

        DB::table('detalle_compras')->where('id_compra', 1)->where('id_producto', 5)->where('id_talla', 4)->decrement('cantidad_restante', 1);
        DB::table('detalle_compras')->where('id_compra', 2)->where('id_producto', 6)->where('id_talla', 4)->decrement('cantidad_restante', 1);

        DB::table('kardex')->insert([
            ['id_producto' => 5, 'id_talla' => 4, 'tipo_movimiento' => 'venta', 'cantidad' => 1, 'fecha' => '2026-02-10 09:20:00', 'referencia' => 'Pedido #3'],
            ['id_producto' => 6, 'id_talla' => 4, 'tipo_movimiento' => 'venta', 'cantidad' => 1, 'fecha' => '2026-02-10 09:20:00', 'referencia' => 'Pedido #3'],
        ]);

        // ─── Pedido 4: cancelado — Luis (id_usuario=6) ────────────────────────
        DB::table('pedidos')->insert([
            'id_usuario'     => 6,
            'id_cupon'       => null,
            'subtotal'       => 79.99,
            'monto_descuento'=> 0,
            'costo_envio'    => 5.00,
            'total'          => 84.99,
            'moneda'         => 'USD',
            'estado_pago'    => 'pagado',
            'paypal_order_id'=> 'PAYPAL-ORDER-00004',
            'paypal_payer_id'=> 'PAYER-00004',
            'estado_pedido'  => 'cancelado',
            'fecha_pedido'   => '2026-01-20 16:00:00',
        ]);

        DB::table('detalle_pedidos')->insert([
            ['id_pedido' => 4, 'id_producto' => 1, 'id_talla' => 3, 'cantidad' => 1, 'precio_unitario' => 79.99],
        ]);

        // Cancelado: stock ya fue restituido (cantidad_restante sin cambio)
        DB::table('kardex')->insert([
            ['id_producto' => 1, 'id_talla' => 3, 'tipo_movimiento' => 'venta',  'cantidad' => 1, 'fecha' => '2026-01-20 16:05:00', 'referencia' => 'Pedido #4'],
            ['id_producto' => 1, 'id_talla' => 3, 'tipo_movimiento' => 'compra', 'cantidad' => 1, 'fecha' => '2026-01-21 08:00:00', 'referencia' => 'Cancelación Pedido #4'],
        ]);

        // ─── Pedido 5: entregado — María (id_usuario=3) segundo pedido ────────
        DB::table('pedidos')->insert([
            'id_usuario'     => 3,
            'id_cupon'       => 3, // PROMO5USD
            'subtotal'       => 44.99,
            'monto_descuento'=> 5.00,
            'costo_envio'    => 0,
            'total'          => 39.99,
            'moneda'         => 'USD',
            'estado_pago'    => 'pagado',
            'paypal_order_id'=> 'PAYPAL-ORDER-00005',
            'paypal_payer_id'=> 'PAYER-00001',
            'estado_pedido'  => 'entregado',
            'fecha_pedido'   => '2026-02-01 11:00:00',
        ]);

        DB::table('detalle_pedidos')->insert([
            ['id_pedido' => 5, 'id_producto' => 10, 'id_talla' => 3, 'cantidad' => 1, 'precio_unitario' => 44.99],
        ]);

        DB::table('detalle_compras')->where('id_compra', 2)->where('id_producto', 10)->where('id_talla', 3)->decrement('cantidad_restante', 1);

        DB::table('kardex')->insert([
            ['id_producto' => 10, 'id_talla' => 3, 'tipo_movimiento' => 'venta', 'cantidad' => 1, 'fecha' => '2026-02-01 11:05:00', 'referencia' => 'Pedido #5'],
        ]);
    }
}
