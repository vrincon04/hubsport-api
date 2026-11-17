<?php

namespace Tests\Feature\Demo;

use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\DatabaseDemoNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoClientContractsTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_search_returns_suggested_users(): void
    {
        $viewer = User::factory()->create();
        $peer = User::factory()->create(['name' => 'Camila Soto']);
        $token = $viewer->createToken('test')->plainTextToken;

        $ids = collect(
            $this->withToken($token)->getJson('/api/v1/search')->assertOk()->json('data.users')
        )->pluck('id');

        $this->assertTrue($ids->contains($peer->id));
        $this->assertFalse($ids->contains($viewer->id));
    }

    public function test_post_likes_expose_user_names(): void
    {
        $author = User::factory()->create();
        $fan = User::factory()->create(['name' => 'Diego Vargas']);
        $post = Post::factory()->create(['user_id' => $author->id]);
        Like::factory()->create([
            'user_id' => $fan->id,
            'likeable_id' => $post->id,
            'likeable_type' => Post::class,
        ]);

        $likes = $this->withToken($author->createToken('test')->plainTextToken)
            ->getJson('/api/v1/posts/'.$post->id)
            ->assertOk()
            ->json('data.likes');

        $this->assertSame($fan->id, $likes[0]['id']);
        $this->assertSame('Diego Vargas', $likes[0]['name']);
    }

    public function test_conversation_includes_other_user(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create(['name' => 'Elena Ruiz']);
        $conversation = Conversation::create();
        $conversation->users()->attach([$me->id, $other->id]);

        $row = collect(
            $this->withToken($me->createToken('test')->plainTextToken)
                ->getJson('/api/v1/chat')
                ->assertOk()
                ->json('data')
        )->first();

        $this->assertSame($other->id, $row['other_user']['id']);
        $this->assertSame('Elena Ruiz', $row['other_user']['name']);
    }

    public function test_accepted_connection_user_is_peer_ulid_not_connection_id(): void
    {
        $me = User::factory()->create();
        $peer = User::factory()->create(['name' => 'Hugo Pena']);
        $connection = Connection::query()->create([
            'user_id' => $me->id,
            'connected_user_id' => $peer->id,
            'status' => 'accepted',
        ]);

        $row = collect(
            $this->withToken($me->createToken('test')->plainTextToken)
                ->getJson('/api/v1/connections')
                ->assertOk()
                ->json('data')
        )->first();

        $this->assertSame($connection->id, $row['id']);
        $this->assertSame($peer->id, $row['user']['id']);
        $this->assertNotEquals((string) $connection->id, (string) $row['user']['id']);
    }

    public function test_notifications_flatten_body_and_type(): void
    {
        $me = User::factory()->create();
        DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => DatabaseDemoNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $me->id,
            'data' => [
                'type' => 'connection',
                'title' => 'Nueva conexion',
                'body' => 'Laura Mendez acepto tu conexion.',
            ],
        ]);

        $row = collect(
            $this->withToken($me->createToken('test')->plainTextToken)
                ->getJson('/api/v1/user/notifications')
                ->assertOk()
                ->json('data')
        )->first();

        $this->assertSame('Laura Mendez acepto tu conexion.', $row['body']);
        $this->assertSame('connection', $row['type']);
    }
}
