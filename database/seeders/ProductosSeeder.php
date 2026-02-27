<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        // id_categoria: 1=Local, 2=Visitante, 3=Tercera, 4=Retro, 5=Selección
        // id_equipo:    1=Real Madrid, 2=Barcelona, 3=Man City, 4=Man United,
        //               5=Bayern, 6=PSG, 7=Juventus, 8=Selección Ecuador,
        //               9=Liga de Quito, 10=Barcelona SC

        $productos = [
            // Real Madrid
            ['sku_base' => 'RM-LOC-25',  'nombre' => 'Real Madrid Local 2024/25',     'precio_venta_base' => 79.99,  'id_categoria' => 1, 'id_equipo' => 1],
            ['sku_base' => 'RM-VIS-25',  'nombre' => 'Real Madrid Visitante 2024/25', 'precio_venta_base' => 79.99,  'id_categoria' => 2, 'id_equipo' => 1],
            // Barcelona
            ['sku_base' => 'BAR-LOC-25', 'nombre' => 'Barcelona Local 2024/25',       'precio_venta_base' => 84.99,  'id_categoria' => 1, 'id_equipo' => 2],
            ['sku_base' => 'BAR-VIS-25', 'nombre' => 'Barcelona Visitante 2024/25',   'precio_venta_base' => 84.99,  'id_categoria' => 2, 'id_equipo' => 2],
            // Manchester City
            ['sku_base' => 'MC-LOC-25',  'nombre' => 'Manchester City Local 2024/25', 'precio_venta_base' => 89.99,  'id_categoria' => 1, 'id_equipo' => 3],
            // Bayern München
            ['sku_base' => 'BAY-LOC-25', 'nombre' => 'Bayern München Local 2024/25',  'precio_venta_base' => 82.99,  'id_categoria' => 1, 'id_equipo' => 5],
            // PSG
            ['sku_base' => 'PSG-LOC-25', 'nombre' => 'PSG Local 2024/25',             'precio_venta_base' => 87.99,  'id_categoria' => 1, 'id_equipo' => 6],
            // Selección Ecuador
            ['sku_base' => 'ECU-LOC-25', 'nombre' => 'Ecuador Local 2024/25',         'precio_venta_base' => 59.99,  'id_categoria' => 5, 'id_equipo' => 8],
            ['sku_base' => 'ECU-VIS-25', 'nombre' => 'Ecuador Visitante 2024/25',     'precio_venta_base' => 59.99,  'id_categoria' => 5, 'id_equipo' => 8],
            // Liga de Quito
            ['sku_base' => 'LDU-LOC-25', 'nombre' => 'Liga de Quito Local 2024/25',   'precio_venta_base' => 44.99,  'id_categoria' => 1, 'id_equipo' => 9],
            // Barcelona SC
            ['sku_base' => 'BSC-LOC-25', 'nombre' => 'Barcelona SC Local 2024/25',    'precio_venta_base' => 44.99,  'id_categoria' => 1, 'id_equipo' => 10],
            // Retro
            ['sku_base' => 'RM-RET-02',  'nombre' => 'Real Madrid Retro 2001/02',     'precio_venta_base' => 69.99,  'id_categoria' => 4, 'id_equipo' => 1],
        ];

        foreach ($productos as &$p) {
            $p['descripcion'] = 'Camiseta oficial ' . $p['nombre'] . '. 100% poliéster, tecnología Dri-Fit.';
            $p['activo'] = true;
        }

        DB::table('productos')->insert($productos);

        // Imagen placeholder para cada producto
        $imgs = [];
        for ($i = 1; $i <= count($productos); $i++) {
            $imgs[] = [
                'id_producto'  => $i,
                'url_imagen'   => 'https://via.placeholder.com/400x400.png?text=Camiseta+' . $i,
                'es_principal' => true,
            ];
        }
        DB::table('imagenes_productos')->insert($imgs);
    }
}
