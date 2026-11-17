<?php

namespace Tests\Feature\Flows;

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
        $organizer = User::factory()->create();
        $athlete = User::factory()->create();
        $sport = Sport::factory()->create(['name' => 'Atletismo']);

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
