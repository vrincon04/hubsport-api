<?php

namespace Tests\Feature\Flows;

use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\SportMetricDefinition;
use App\Models\User;
use Database\Seeders\ScoutMetricDefinitionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ScoutMetricsFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_catalog_physical_metrics_and_sport_lock_flow(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $baseball = Sport::query()->create(['name' => 'béisbol']);
        $football = Sport::query()->create(['name' => 'fútbol']);

        $this->seed(ScoutMetricDefinitionsSeeder::class);

        $definitions = SportMetricDefinition::query()->where('sport_id', $baseball->id)->get();
        $this->assertGreaterThan(0, $definitions->count());

        $this->getJson("/api/v1/sport/{$baseball->id}/metric-definitions")
            ->assertSuccessful()
            ->assertJsonPath('success', true);

        $user = User::factory()->create(['name' => 'Scout Athlete']);
        Profile::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Luis',
            'last_name' => 'Peña',
            'country_id' => $country->id,
            'city' => 'Santo Domingo',
            'sport_id' => $baseball->id,
            'sport_locked_at' => now(),
            'profile_type' => 'athlete',
        ]);

        $this->asApiUser($user)
            ->putJson("/api/v1/user/{$user->id}", [
                'profile' => [
                    'height_cm' => 185,
                    'weight_kg' => 82.5,
                    'wingspan_cm' => 190,
                    'laterality' => 'right',
                    'current_team' => 'Academia Norte',
                ],
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.profile.height_cm', '185.00');

        $def = $definitions->first();
        $this->asApiUser($user)
            ->putJson('/api/v1/user/me/metrics', [
                'metrics' => [
                    [
                        'definition_id' => $def->id,
                        'value_numeric' => 0.312,
                        'recorded_at' => '2026-11-01',
                        'source' => 'self_reported',
                    ],
                ],
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.0.verification_status', 'self_reported');

        $this->asApiUser($user)
            ->getJson('/api/v1/user/me/metrics')
            ->assertSuccessful()
            ->assertJsonPath('meta.empty', false);

        $this->asApiUser($user)
            ->putJson("/api/v1/user/{$user->id}", [
                'profile' => [
                    'sport_id' => $football->id,
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['profile.sport_id']);

        $this->asApiUser($user)
            ->postJson('/api/v1/user/me/competition-results', [
                'competed_at' => '2026-10-10',
                'competition' => 'Torneo Nacional',
                'mark' => '0.312 AVG',
                'placement' => '2',
            ])
            ->assertCreated();

        $video = $this->asApiUser($user)
            ->postJson('/api/v1/user/me/videos', [
                'title' => 'Highlights 2026',
                'video_url' => 'https://example.test/video.mp4',
                'kind' => 'game',
            ])
            ->assertCreated();

        $videoId = $video->json('data.id');
        $this->asApiUser($user)
            ->deleteJson("/api/v1/user/me/videos/{$videoId}")
            ->assertSuccessful();

        $metricId = $this->asApiUser($user)->getJson('/api/v1/user/me/metrics')->json('data.0.id');

        $coach = User::factory()->create(['name' => 'Coach Verify']);
        Profile::query()->create([
            'user_id' => $coach->id,
            'first_name' => 'Coach',
            'last_name' => 'Verify',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $baseball->id,
            'profile_type' => 'coach',
            'account_status' => 'active',
            'birth_date' => '1980-01-01',
        ]);

        $this->asApiUser($coach)
            ->patchJson("/api/v1/user/{$user->id}/metrics/{$metricId}/verify", [
                'verification_status' => 'coach_verified',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.verification_status', 'coach_verified');

        $fan = User::factory()->create();
        Profile::query()->create([
            'user_id' => $fan->id,
            'first_name' => 'Fan',
            'last_name' => 'User',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $baseball->id,
            'profile_type' => 'fan',
            'account_status' => 'active',
            'birth_date' => '1995-01-01',
        ]);

        $this->asApiUser($fan)
            ->patchJson("/api/v1/user/{$user->id}/metrics/{$metricId}/verify", [
                'verification_status' => 'coach_verified',
            ])
            ->assertForbidden();
    }
}
