<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\SportMetricDefinition;
use Illuminate\Database\Seeder;

class ScoutMetricDefinitionsSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'béisbol' => [
                ['Bateo', 'avg', 'AVG', null, false],
                ['Bateo', 'obp', 'OBP', null, false],
                ['Bateo', 'k_pct', 'K%', '%', true],
                ['Poder', 'exit_velo', 'Velocidad de salida', 'mph', false],
                ['Poder', 'slg', 'SLG', null, false],
                ['Velocidad', 'sixty_yard', '60 yardas', 's', true],
                ['Brazo', 'throw_velo', 'Velocidad de tiro', 'mph', false],
                ['Pitching', 'fb_velo', 'Velocidad de recta', 'mph', false],
                ['Pitching', 'era', 'ERA', null, true],
                ['Pitching', 'whip', 'WHIP', null, true],
            ],
            'voleibol' => [
                ['Saque', 'aces', 'Aces', null, false],
                ['Saque', 'serve_eff', 'Eficiencia de saque', '%', false],
                ['Recepción', 'perfect_pass', 'Recepción perfecta', '%', false],
                ['Ataque', 'kill_pct', 'Porcentaje de ataque', '%', false],
                ['Físico', 'attack_reach', 'Alcance de ataque', 'cm', false],
                ['Físico', 'block_reach', 'Alcance de bloqueo', 'cm', false],
            ],
            'atletismo' => [
                ['Marcas', 'pb', 'Marca personal (PB)', null, true],
                ['Marcas', 'sb', 'Marca de temporada (SB)', null, true],
                ['Marcas', 'wa_points', 'Puntos World Athletics', null, false],
                ['Físico', 'cmj', 'Salto vertical CMJ', 'cm', false],
                ['Físico', 'vo2max', 'VO₂ máx', 'ml/kg/min', false],
                ['Sprint', 'reaction', 'Reacción', 's', true],
            ],
            'fútbol' => [
                ['Finalización', 'goals', 'Goles', null, false],
                ['Finalización', 'xg', 'xG', null, false],
                ['Creación', 'assists', 'Asistencias', null, false],
                ['Creación', 'xa', 'xA', null, false],
                ['Defensa', 'tackles', 'Duelos ganados', null, false],
                ['Físico', 'top_speed', 'Velocidad punta', 'km/h', false],
            ],
            'baloncesto' => [
                ['Eficiencia', 'pir', 'PIR', null, false],
                ['Tiro', 'ts_pct', 'TS%', '%', false],
                ['Tiro', 'efg_pct', 'eFG%', '%', false],
                ['Producción', 'ppg', 'Puntos por partido', null, false],
                ['Producción', 'rpg', 'Rebotes por partido', null, false],
                ['Producción', 'apg', 'Asistencias por partido', null, false],
            ],
            'natación' => [
                ['Marcas', 'official_time', 'Marca oficial', 's', true],
                ['Marcas', 'waq_points', 'Puntos World Aquatics', null, false],
                ['Técnica', 'swolf', 'SWOLF', null, true],
                ['Técnica', 'stroke_distance', 'Distancia por brazada', 'm', false],
                ['Salida', 'reaction_start', 'Reacción de salida', 's', true],
            ],
        ];

        foreach ($catalog as $sportName => $metrics) {
            $sport = Sport::query()->firstOrCreate(
                ['name' => $sportName],
                ['description' => 'Deporte scout HubSport']
            );

            foreach ($metrics as $i => [$group, $key, $label, $unit, $lower]) {
                SportMetricDefinition::query()->updateOrCreate(
                    [
                        'sport_id' => $sport->id,
                        'key' => $key,
                    ],
                    [
                        'metric_group' => $group,
                        'label' => $label,
                        'unit' => $unit,
                        'lower_is_better' => $lower,
                        'sort_order' => $i,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
