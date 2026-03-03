<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->insert([
            [
                'nombre'         => 'Admin',
                'apellido'       => 'Principal',
                'email'          => 'admin@tienda.com',
                'password'       => Hash::make('password'),
                'telefono'       => '0991000001',
                'direccion_envio'=> 'Av. Principal 100, Quito',
                'rol'            => 'admin',
            ],
            [
                'nombre'         => 'Carlos',
                'apellido'       => 'Bodega',
                'email'          => 'almacen@tienda.com',
                'password'       => Hash::make('password'),
                'telefono'       => '0991000002',
                'direccion_envio'=> 'Calle Bodega 45, Guayaquil',
                'rol'            => 'almacen',
            ],
            [
                'nombre'         => 'María',
                'apellido'       => 'García',
                'email'          => 'maria@cliente.com',
                'password'       => Hash::make('password'),
                'telefono'       => '0991111111',
                'direccion_envio'=> 'Calle Los Rosales 23, Quito',
                'rol'            => 'cliente',
            ],
            [
                'nombre'         => 'Juan',
                'apellido'       => 'Pérez',
                'email'          => 'juan@cliente.com',
                'password'       => Hash::make('password'),
                'telefono'       => '0992222222',
                'direccion_envio'=> 'Av. Universitaria 56, Cuenca',
                'rol'            => 'cliente',
            ],
            [
                'nombre'         => 'Ana',
                'apellido'       => 'Torres',
                'email'          => 'ana@cliente.com',
                'password'       => Hash::make('password'),
                'telefono'       => '0993333333',
                'direccion_envio'=> 'Calle Colón 78, Ambato',
                'rol'            => 'cliente',
            ],
            [
                'nombre'         => 'Luis',
                'apellido'       => 'Mendoza',
                'email'          => 'luis@cliente.com',
                'password'       => Hash::make('password'),
                'telefono'       => '0994444444',
                'direccion_envio'=> 'Av. Las Americas 12, Guayaquil',
                'rol'            => 'cliente',
            ],
        ]);
    }
}
