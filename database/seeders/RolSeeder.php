<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'deportista', 'guard_name' => 'api']);
        Role::create(['name' => 'entrenador', 'guard_name' => 'api']);
        Role::create(['name' => 'reclutador', 'guard_name' => 'api']);
    }
}
