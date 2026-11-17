<?php

namespace Tests\Feature\Flows;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class SponsorshipsFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_create_list_and_show_sponsorship_flow(): void
    {
        $brand = User::factory()->create();
        $viewer = User::factory()->create();
        $sport = Sport::factory()->create(['name' => 'Fútbol']);

        $create = $this->asApiUser($brand)->postJson('/api/v1/sponsorships', [
            'brand_name' => 'Impulso Deportivo',
            'sport_id' => $sport->id,
            'sponsorship_type' => 'Equipamiento',
            'requirements' => 'Atletas con proyección nacional.',
            'benefits' => 'Kit completo y exposición de marca.',
            'contact' => 'alianzas@impulso.test',
        ]);

        $create->assertCreated();
        $id = $create->json('data.id');
        $this->assertNotEmpty($id);

        $this->asApiUser($viewer)
            ->getJson('/api/v1/sponsorships')
            ->assertOk();

        $this->asApiUser($viewer)
            ->getJson("/api/v1/sponsorships/{$id}")
            ->assertOk();
    }
}
