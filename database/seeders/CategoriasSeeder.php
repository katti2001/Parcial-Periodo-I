<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categorias')->insert([
            ['nombre' => 'Camiseta Local',    'descripcion' => 'Camiseta oficial de local del equipo'],
            ['nombre' => 'Camiseta Visitante', 'descripcion' => 'Camiseta oficial de visitante del equipo'],
            ['nombre' => 'Camiseta Tercera',   'descripcion' => 'Camiseta alternativa del equipo'],
            ['nombre' => 'Camiseta Retro',     'descripcion' => 'Edición histórica o retro'],
            ['nombre' => 'Camiseta Selección', 'descripcion' => 'Camiseta de selección nacional'],
        ]);
    }
}
