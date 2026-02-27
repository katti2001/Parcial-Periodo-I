<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('proveedores')->insert([
            [
                'nombre_empresa'   => 'Deportes Premium S.A.',
                'contacto_nombre'  => 'Roberto Salinas',
                'telefono'         => '022345678',
                'email'            => 'ventas@deportespremium.com',
                'direccion'        => 'Zona Industrial Norte, Quito',
            ],
            [
                'nombre_empresa'   => 'Sport Import Cía. Ltda.',
                'contacto_nombre'  => 'Verónica Mora',
                'telefono'         => '042987654',
                'email'            => 'pedidos@sportimport.ec',
                'direccion'        => 'Puerto Marítimo, Guayaquil',
            ],
            [
                'nombre_empresa'   => 'Fútbol Total',
                'contacto_nombre'  => 'Diego Castillo',
                'telefono'         => '072112233',
                'email'            => 'diego@futboltotal.com',
                'direccion'        => 'Av. Remigio Crespo, Cuenca',
            ],
        ]);
    }
}
