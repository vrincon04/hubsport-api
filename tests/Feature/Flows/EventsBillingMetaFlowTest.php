<?php

namespace Tests\Feature\Flows;

use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class EventsBillingMetaFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_create_participate_and_save_event_flow(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::factory()->create(['name' => 'Atletismo']);
        $organizer = User::factory()->create();
        Profile::query()->create([
            'user_id' => $organizer->id,
            'first_name' => 'Org',
            'last_name' => 'Coach',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'coach',
            'account_status' => 'active',
            'birth_date' => '1980-01-01',
        ]);
        $athlete = User::factory()->create();
        Profile::query()->create([
            'user_id' => $athlete->id,
            'first_name' => 'Ath',
            'last_name' => 'Lete',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => '2000-01-01',
        ]);

        $create = $this->asApiUser($organizer)->postJson('/api/v1/events', [
            'name' => 'Carrera 10K',
            'event_date' => now()->addDays(10)->toDateString(),
            'event_time' => '08:00',
            'location' => 'Parque Central',
            'city' => 'Santo Domingo',
            'country' => 'DO',
            'sport_id' => $sport->id,
            'description' => 'Carrera popular de 10 kilómetros.',
            'organizer_name' => 'Club Horizonte',
            'organizer_contact' => 'eventos@club.test',
        ]);

        $create->assertCreated();
        $eventId = $create->json('data.id');
        $this->assertNotEmpty($eventId);

        $this->asApiUser($athlete)
            ->getJson('/api/v1/events')
            ->assertOk();

        $this->asApiUser($athlete)
            ->getJson("/api/v1/events/{$eventId}")
            ->assertOk();

        $this->asApiUser($athlete)
            ->postJson("/api/v1/events/{$eventId}/participate")
            ->assertCreated();

        $this->asApiUser($athlete)
            ->postJson("/api/v1/events/{$eventId}/save")
            ->assertSuccessful();

        $this->asApiUser($athlete)
            ->deleteJson("/api/v1/events/{$eventId}/unsave")
            ->assertSuccessful();
    }

    public function test_billing_summary_and_meta_endpoints(): void
    {
        $user = User::factory()->create();

        $this->asApiUser($user)
            ->getJson('/api/v1/billing/summary')
            ->assertOk()
            ->assertJsonStructure(['data' => ['subscription', 'payment_methods', 'invoices']]);

        $this->getJson('/api/v1/countries')->assertSuccessful();
        $this->getJson('/api/v1/help')->assertSuccessful();
        $this->getJson('/api/v1/sport')->assertSuccessful();
        $this->getJson('/api/v1/role')->assertSuccessful();
    }
}
