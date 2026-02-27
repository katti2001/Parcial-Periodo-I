<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquiposSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('equipos')->insert([
            ['nombre' => 'Real Madrid',       'pais' => 'España'],
            ['nombre' => 'Barcelona',          'pais' => 'España'],
            ['nombre' => 'Manchester City',    'pais' => 'Inglaterra'],
            ['nombre' => 'Manchester United',  'pais' => 'Inglaterra'],
            ['nombre' => 'Bayern München',     'pais' => 'Alemania'],
            ['nombre' => 'PSG',                'pais' => 'Francia'],
            ['nombre' => 'Juventus',           'pais' => 'Italia'],
            ['nombre' => 'Selección Ecuador',  'pais' => 'Ecuador'],
            ['nombre' => 'Liga de Quito',      'pais' => 'Ecuador'],
            ['nombre' => 'Barcelona SC',       'pais' => 'Ecuador'],
        ]);
    }
}
