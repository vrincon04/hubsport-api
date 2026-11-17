<?php

namespace Tests\Feature\Flows;

use App\Enums\PostStatusEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class FeedUnlikeDeleteFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_unlike_and_delete_post_flow(): void
    {
        $author = User::factory()->create();
        $fan = User::factory()->create();

        $postId = $this->asApiUser($author)->postJson('/api/v1/posts', [
            'title' => 'Post temporal',
            'body' => 'Contenido a eliminar',
            'status' => PostStatusEnum::PUBLISHED->value,
            'published_at' => now()->toDateTimeString(),
        ])->assertCreated()->json('data.id');

        $this->asApiUser($fan)
            ->postJson("/api/v1/posts/{$postId}/like")
            ->assertNoContent();

        $this->asApiUser($fan)
            ->deleteJson("/api/v1/posts/{$postId}/unlike")
            ->assertNoContent();

        $this->asApiUser($author)
            ->deleteJson("/api/v1/posts/{$postId}")
            ->assertSuccessful();

        $this->asApiUser($fan)
            ->getJson("/api/v1/posts/{$postId}")
            ->assertNotFound();
    }
}
