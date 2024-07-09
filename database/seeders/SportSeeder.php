<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Sport::create(['name' => 'natación']);
        Sport::create(['name' => 'fútbol']);
        Sport::create(['name' => 'voleibol']);
        Sport::create(['name' => 'baloncesto']);
        Sport::create(['name' => 'tenis']);
        Sport::create(['name' => 'bádminton']);
        Sport::create(['name' => 'béisbol']);
        Sport::create(['name' => 'balonmano']);
        Sport::create(['name' => 'hockey']);
        Sport::create(['name' => 'rugby']);
    }
}
