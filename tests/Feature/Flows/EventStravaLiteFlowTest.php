<?php

namespace Tests\Feature\Flows;

use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class EventStravaLiteFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_capacity_participants_calendar_and_activity_link(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::factory()->create(['name' => 'Atletismo']);

        $organizer = User::factory()->create(['name' => 'Org Events']);
        Profile::query()->create([
            'user_id' => $organizer->id,
            'first_name' => 'Org',
            'last_name' => 'Events',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'event_organizer',
            'account_status' => 'active',
            'birth_date' => '1985-01-01',
        ]);

        $athleteA = User::factory()->create(['name' => 'Athlete A']);
        Profile::query()->create([
            'user_id' => $athleteA->id,
            'first_name' => 'Ath',
            'last_name' => 'A',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => '2000-01-01',
        ]);

        $athleteB = User::factory()->create(['name' => 'Athlete B']);
        Profile::query()->create([
            'user_id' => $athleteB->id,
            'first_name' => 'Ath',
            'last_name' => 'B',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => '2001-01-01',
        ]);

        $create = $this->asApiUser($organizer)->postJson('/api/v1/events', [
            'name' => '5K Parque',
            'event_type' => 'race',
            'event_date' => now()->addDays(5)->toDateString(),
            'event_time' => '07:30',
            'location' => 'Parque Mirador',
            'route_text' => 'Vuelta al lago 5 km',
            'city' => 'Santo Domingo',
            'country' => 'DO',
            'sport_id' => $sport->id,
            'description' => 'Carrera comunitaria.',
            'organizer_name' => 'Club Horizonte',
            'organizer_contact' => 'eventos@club.test',
            'capacity' => 1,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.capacity', 1)
            ->assertJsonPath('data.event_type', 'race');

        $eventId = $create->json('data.id');

        $this->asApiUser($athleteA)
            ->postJson("/api/v1/events/{$eventId}/participate")
            ->assertCreated();

        $this->asApiUser($athleteB)
            ->postJson("/api/v1/events/{$eventId}/participate")
            ->assertStatus(422)
            ->assertJsonPath('error', 'capacity_full');

        $show = $this->asApiUser($athleteA)->getJson("/api/v1/events/{$eventId}");
        $show->assertOk()
            ->assertJsonPath('data.is_participating', true)
            ->assertJsonPath('data.is_full', true);
        $this->assertCount(1, $show->json('data.participants'));

        $this->asApiUser($athleteA)
            ->get("/api/v1/events/{$eventId}/calendar.ics")
            ->assertOk()
            ->assertHeader('content-type', 'text/calendar; charset=utf-8')
            ->assertSee('BEGIN:VCALENDAR', false)
            ->assertSee('5K Parque', false);

        $this->asApiUser($athleteA)
            ->postJson("/api/v1/events/{$eventId}/participants/me/activity", [
                'activity_url' => 'https://www.strava.com/activities/123456',
                'activity_note' => 'PR personal',
            ])
            ->assertOk()
            ->assertJsonPath('data.activity_url', 'https://www.strava.com/activities/123456');

        $this->asApiUser($athleteA)
            ->deleteJson("/api/v1/events/{$eventId}/participate")
            ->assertSuccessful();

        $this->asApiUser($athleteB)
            ->postJson("/api/v1/events/{$eventId}/participate")
            ->assertCreated();
    }
}
