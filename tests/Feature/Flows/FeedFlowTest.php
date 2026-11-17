<?php

namespace Tests\Feature\Flows;

use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class FeedFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_create_list_like_comment_and_show_post_flow(): void
    {
        $author = User::factory()->create();
        $fan = User::factory()->create();

        $create = $this->asApiUser($author)->postJson('/api/v1/posts', [
            'title' => 'Entrenamiento matutino',
            'body' => 'Hoy corrí 10km en la pista.',
            'status' => PostStatusEnum::PUBLISHED->value,
            'published_at' => now()->toDateTimeString(),
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.title', 'Entrenamiento matutino');

        $postId = $create->json('data.id');
        $this->assertNotEmpty($postId);

        $this->asApiUser($fan)
            ->getJson('/api/v1/posts')
            ->assertOk();

        $this->asApiUser($fan)
            ->postJson("/api/v1/posts/{$postId}/like")
            ->assertSuccessful();

        $this->asApiUser($fan)
            ->postJson("/api/v1/posts/{$postId}/comments", [
                'content' => '¡Gran ritmo!',
            ])
            ->assertCreated();

        $show = $this->asApiUser($author)->getJson("/api/v1/posts/{$postId}");
        $show->assertOk()->assertJsonPath('data.id', $postId);

        $this->assertDatabaseHas('posts', [
            'id' => $postId,
            'user_id' => $author->id,
            'title' => 'Entrenamiento matutino',
        ]);

        $this->assertTrue(Post::query()->whereKey($postId)->exists());
    }
}
