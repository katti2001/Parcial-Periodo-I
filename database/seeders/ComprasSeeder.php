<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprasSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Compra 1: recibida — stock inicial principal ─────────────────────
        DB::table('compras')->insert([
            'id_proveedor'             => 1,
            'fecha_compra'             => '2025-11-10 09:00:00',
            'total_compra'             => 2520.00,
            'numero_factura_proveedor' => 'FAC-2025-001',
            'estado'                   => 'recibido',
        ]);

        // id_compra = 1: productos 1–6, tallas S/M/L/XL
        $detalles1 = [
            // Real Madrid Local (id_producto=1)
            [1, 1, 2, 10, 10, 42.00],  // S
            [1, 1, 3, 15, 15, 42.00],  // M
            [1, 1, 4, 15, 15, 42.00],  // L
            [1, 1, 5, 10, 10, 42.00],  // XL
            // Barcelona Local (id_producto=3)
            [1, 3, 3, 12, 12, 45.00],
            [1, 3, 4, 12, 12, 45.00],
            [1, 3, 5, 8,  8,  45.00],
            // Man City Local (id_producto=5)
            [1, 5, 3, 10, 10, 48.00],
            [1, 5, 4, 10, 10, 48.00],
            // Selección Ecuador Local (id_producto=8)
            [1, 8, 2, 20, 20, 30.00],
            [1, 8, 3, 25, 25, 30.00],
            [1, 8, 4, 20, 20, 30.00],
            [1, 8, 5, 15, 15, 30.00],
        ];

        // ─── Compra 2: recibida — stock secundario ────────────────────────────
        DB::table('compras')->insert([
            'id_proveedor'             => 2,
            'fecha_compra'             => '2026-01-05 10:30:00',
            'total_compra'             => 1890.00,
            'numero_factura_proveedor' => 'FAC-2026-004',
            'estado'                   => 'recibido',
        ]);

        $detalles2 = [
            // Real Madrid Visitante (id_producto=2)
            [2, 2, 3, 10, 10, 42.00],
            [2, 2, 4, 10, 10, 42.00],
            [2, 2, 5, 8,  8,  42.00],
            // Barcelona Visitante (id_producto=4)
            [2, 4, 3, 8,  8,  45.00],
            [2, 4, 4, 8,  8,  45.00],
            // Bayern Local (id_producto=6)
            [2, 6, 3, 10, 10, 44.00],
            [2, 6, 4, 10, 10, 44.00],
            [2, 6, 5, 8,  8,  44.00],
            // PSG Local (id_producto=7)
            [2, 7, 3, 10, 10, 47.00],
            [2, 7, 4, 10, 10, 47.00],
            // LDU Local (id_producto=10)
            [2, 10, 3, 15, 15, 22.00],
            [2, 10, 4, 15, 15, 22.00],
            // Barcelona SC Local (id_producto=11)
            [2, 11, 3, 15, 15, 22.00],
            [2, 11, 4, 15, 15, 22.00],
        ];

        // ─── Compra 3: solicitada (en camino, sin stock aún) ──────────────────
        DB::table('compras')->insert([
            'id_proveedor'             => 3,
            'fecha_compra'             => '2026-02-20 08:00:00',
            'total_compra'             => 960.00,
            'numero_factura_proveedor' => 'FAC-2026-011',
            'estado'                   => 'solicitado',
        ]);

        $detalles3 = [
            // Ecuador Visitante (id_producto=9)
            [3, 9, 3, 20, 20, 30.00],
            [3, 9, 4, 20, 20, 30.00],
            // Real Madrid Retro (id_producto=12)
            [3, 12, 3, 8,  8,  37.00],
            [3, 12, 4, 8,  8,  37.00],
        ];

        // ─── Compra 4: cancelada ──────────────────────────────────────────────
        DB::table('compras')->insert([
            'id_proveedor'             => 1,
            'fecha_compra'             => '2025-12-01 14:00:00',
            'total_compra'             => 450.00,
            'numero_factura_proveedor' => null,
            'estado'                   => 'cancelado',
        ]);

        $detalles4 = [
            [4, 1, 4, 10, 10, 42.00],
            [4, 3, 4, 5,  5,  45.00],
        ];

        $allDetalles = array_merge($detalles1, $detalles2, $detalles3, $detalles4);

        foreach ($allDetalles as $d) {
            DB::table('detalle_compras')->insert([
                'id_compra'         => $d[0],
                'id_producto'       => $d[1],
                'id_talla'          => $d[2],
                'cantidad_comprada' => $d[3],
                'cantidad_restante' => $d[4],
                'costo_unitario'    => $d[5],
            ]);
        }

        // ─── Kardex para las compras recibidas ────────────────────────────────
        $kardexEntries = [];
        $comprasRecibidas = array_merge($detalles1, $detalles2);
        foreach ($comprasRecibidas as $d) {
            $kardexEntries[] = [
                'id_producto'     => $d[1],
                'id_talla'        => $d[2],
                'tipo_movimiento' => 'compra',
                'cantidad'        => $d[3],
                'fecha'           => $d[0] === 1 ? '2025-11-10 09:30:00' : '2026-01-05 11:00:00',
                'referencia'      => 'Compra #' . $d[0],
            ];
        }
        DB::table('kardex')->insert($kardexEntries);
    }
}
