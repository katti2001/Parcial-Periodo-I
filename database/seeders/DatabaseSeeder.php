<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoriasSeeder::class,
            EquiposSeeder::class,
            TallasSeeder::class,
            UsuariosSeeder::class,
            ProductosSeeder::class,
            ProveedoresSeeder::class,
            ComprasSeeder::class,
            CuponesSeeder::class,
            PedidosSeeder::class,
            DevolucionesSeeder::class,
        ]);
    }
}
