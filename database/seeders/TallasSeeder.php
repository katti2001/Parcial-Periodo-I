<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TallasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tallas')->insert([
            ['nombre' => 'XS'],
            ['nombre' => 'S'],
            ['nombre' => 'M'],
            ['nombre' => 'L'],
            ['nombre' => 'XL'],
            ['nombre' => 'XXL'],
        ]);
    }
}
