<?php

namespace Tests\Feature\Flows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class SearchFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_search_finds_users_by_name_and_excludes_viewer(): void
    {
        $viewer = User::factory()->create(['name' => 'Viewer Search']);
        $match = User::factory()->create(['name' => 'Camila Atleta']);
        User::factory()->create(['name' => 'Otro Nombre']);

        $response = $this->asApiUser($viewer)->getJson('/api/v1/search?q=Camila');

        $response->assertOk();
        $userIds = collect($response->json('data.users'))->pluck('id');

        $this->assertTrue($userIds->contains($match->id));
        $this->assertFalse($userIds->contains($viewer->id));
    }

    public function test_empty_search_returns_suggestions_envelope(): void
    {
        $viewer = User::factory()->create();
        User::factory()->create(['name' => 'Sugerido Uno']);

        $this->asApiUser($viewer)
            ->getJson('/api/v1/search')
            ->assertOk()
            ->assertJsonStructure(['data' => ['users']]);
    }
}
