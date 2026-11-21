<?php

namespace Tests\Feature\Flows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ChatFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_open_conversation_send_and_read_messages_flow(): void
    {
        $me = User::factory()->create(['name' => 'Chat Me']);
        $peer = User::factory()->create(['name' => 'Chat Peer']);

        $create = $this->asApiUser($me)->postJson('/api/v1/chat', [
            'user_id' => $peer->id,
        ]);

        $create->assertCreated();
        $conversationId = $create->json('data.id') ?? $create->json('id');
        $this->assertNotEmpty($conversationId);

        $send = $this->asApiUser($me)->postJson("/api/v1/chat/{$conversationId}/messages", [
            'text' => 'Hola, ¿entrenamos mañana?',
        ]);

        $send->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.text', 'Hola, ¿entrenamos mañana?')
            ->assertJsonPath('data.sender_id', $me->id);

        $this->asApiUser($peer)
            ->getJson("/api/v1/chat/{$conversationId}")
            ->assertOk()
            ->assertJsonFragment(['text' => 'Hola, ¿entrenamos mañana?']);

        $list = $this->asApiUser($me)->getJson('/api/v1/chat');
        $list->assertOk();
        $ids = collect($list->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($conversationId));
    }
}
