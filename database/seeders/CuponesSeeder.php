<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuponesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cupones')->insert([
            [
                'codigo'            => 'BIENVENIDO10',
                'tipo'              => 'porcentaje',
                'valor'             => 10.00,
                'fecha_vencimiento' => '2026-12-31',
                'activo'            => true,
            ],
            [
                'codigo'            => 'DESCUENTO20',
                'tipo'              => 'porcentaje',
                'valor'             => 20.00,
                'fecha_vencimiento' => '2026-06-30',
                'activo'            => true,
            ],
            [
                'codigo'            => 'PROMO5USD',
                'tipo'              => 'fixed',
                'valor'             => 5.00,
                'fecha_vencimiento' => '2026-03-31',
                'activo'            => true,
            ],
            [
                'codigo'            => 'VERANO15',
                'tipo'              => 'porcentaje',
                'valor'             => 15.00,
                'fecha_vencimiento' => '2025-08-31',
                'activo'            => false,
            ],
        ]);
    }
}
