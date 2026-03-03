<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevolucionesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('devoluciones')->insert([
            'id_pedido'        => 1,
            'id_usuario'       => 3,
            'estado'           => 'solicitado',
            'motivo'           => 'talla_incorrecta',
            'descripcion'      => 'La talla L me quedó grande, necesito talla M.',
            'monto_reembolso'  => null,
            'paypal_refund_id' => null,
            'notas_admin'      => null,
            'fecha_solicitud'  => '2026-02-05 10:00:00',
            'fecha_resolucion' => null,
            'created_at'       => '2026-02-05 10:00:00',
            'updated_at'       => '2026-02-05 10:00:00',
        ]);

        DB::table('detalle_devoluciones')->insert([
            [
                'id_devolucion'    => 1,
                'id_detalle_pedido'=> 3,
                'cantidad_devuelta'=> 1,
                'created_at'       => '2026-02-05 10:00:00',
                'updated_at'       => '2026-02-05 10:00:00',
            ],
        ]);

        DB::table('devoluciones')->insert([
            'id_pedido'        => 5,
            'id_usuario'       => 3,
            'estado'           => 'aprobado',
            'motivo'           => 'no_corresponde_descripcion',
            'descripcion'      => 'La camiseta recibida tiene colores diferentes a los mostrados en la foto.',
            'monto_reembolso'  => 44.99,
            'paypal_refund_id' => 'REFUND-PP-2026-001',
            'notas_admin'      => 'Reembolso procesado. Pedimos disculpas por el inconveniente.',
            'fecha_solicitud'  => '2026-02-10 09:00:00',
            'fecha_resolucion' => '2026-02-12 15:30:00',
            'created_at'       => '2026-02-10 09:00:00',
            'updated_at'       => '2026-02-12 15:30:00',
        ]);

        DB::table('detalle_devoluciones')->insert([
            [
                'id_devolucion'    => 2,
                'id_detalle_pedido'=> 8,
                'cantidad_devuelta'=> 1,
                'created_at'       => '2026-02-10 09:00:00',
                'updated_at'       => '2026-02-10 09:00:00',
            ],
        ]);
    }
}
