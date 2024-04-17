<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar roles
        DB::table('roles')->insert([
            'name' => 'Administrador',
        ]);

        DB::table('roles')->insert([
            'name' => 'Usuario',
        ]);
    }
}
