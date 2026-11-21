<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $sports = [
            'natación',
            'fútbol',
            'voleibol',
            'baloncesto',
            'béisbol',
            'atletismo',
            'tenis',
            'bádminton',
            'balonmano',
            'hockey',
            'rugby',
        ];

        foreach ($sports as $name) {
            Sport::query()->firstOrCreate(['name' => $name]);
        }

        Sport::query()->firstOrCreate(
            ['name' => 'Fórmula 1'],
            ['description' => 'Automovilismo de velocidad']
        );
    }
}
