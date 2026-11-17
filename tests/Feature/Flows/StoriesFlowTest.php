<?php

namespace Tests\Feature\Flows;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class StoriesFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_create_and_list_stories_flow(): void
    {
        $author = User::factory()->create();
        $follower = User::factory()->create();

        Connection::query()->create([
            'user_id' => $follower->id,
            'connected_user_id' => $author->id,
            'status' => 'accepted',
        ]);

        $create = $this->asApiUser($author)->postJson('/api/v1/stories', [
            'type' => 'image',
            'media_url' => 'https://example.com/story.jpg',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.type', 'image');

        $this->asApiUser($author)
            ->getJson('/api/v1/stories')
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->asApiUser($follower)
            ->getJson('/api/v1/stories')
            ->assertOk();
    }
}
