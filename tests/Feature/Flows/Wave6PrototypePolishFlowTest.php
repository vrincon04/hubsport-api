<?php

namespace Tests\Feature\Flows;

use App\Models\Country;
use App\Models\News;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class Wave6PrototypePolishFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_post_type_location_activity_and_news_sport_filter(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $football = Sport::factory()->create(['name' => 'fútbol']);
        $baseball = Sport::factory()->create(['name' => 'béisbol']);

        $athlete = User::factory()->create();
        Profile::query()->create([
            'user_id' => $athlete->id,
            'first_name' => 'Ath',
            'last_name' => 'Lete',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $football->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => '2000-01-01',
        ]);

        $create = $this->asApiUser($athlete)->postJson('/api/v1/posts', [
            'title' => 'Entrenamiento matutino',
            'body' => 'Sesión de velocidad',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'post_type' => 'training',
            'location' => 'Pista Olímpica',
            'activity_url' => 'https://www.strava.com/activities/999',
        ]);

        $create->assertSuccessful()
            ->assertJsonPath('data.post_type', 'training')
            ->assertJsonPath('data.location', 'Pista Olímpica')
            ->assertJsonPath('data.activity_url', 'https://www.strava.com/activities/999');

        News::query()->create([
            'sport_id' => $football->id,
            'title' => 'Golazo fútbol',
            'content' => 'Noticia de fútbol',
        ]);
        News::query()->create([
            'sport_id' => $baseball->id,
            'title' => 'Jonrón',
            'content' => 'Noticia de béisbol',
        ]);

        $filtered = $this->asApiUser($athlete)
            ->getJson('/api/v1/sport/news?sport_id='.$football->id)
            ->assertSuccessful();

        $payload = $filtered->json('data') ?? $filtered->json();
        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }
        $titles = collect(is_array($payload) ? $payload : [])
            ->map(fn ($row) => is_array($row) ? ($row['title'] ?? null) : null)
            ->filter()
            ->values();

        $this->assertTrue($titles->contains('Golazo fútbol'));
        $this->assertFalse($titles->contains('Jonrón'));
    }
}
