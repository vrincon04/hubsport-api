<?php

namespace Tests\Feature\Chat;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_send_message_with_text_contract(): void
    {
        [$sender, $recipient] = User::factory()->count(2)->create();
        $conversation = Conversation::create();
        $conversation->users()->attach([$sender->id, $recipient->id]);

        $response = $this->actingAs($sender)
            ->postJson("/api/v1/chat/{$conversation->id}/messages", [
                'text' => 'Hola desde HubSport',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.text', 'Hola desde HubSport')
            ->assertJsonPath('data.sender_id', $sender->id);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'text' => 'Hola desde HubSport',
        ]);
    }

    public function test_participant_can_send_message_with_legacy_content_contract(): void
    {
        [$sender, $recipient] = User::factory()->count(2)->create();
        $conversation = Conversation::create();
        $conversation->users()->attach([$sender->id, $recipient->id]);

        $response = $this->actingAs($sender)
            ->postJson("/api/v1/chat/{$conversation->id}/messages", [
                'content' => 'Mensaje desde app anterior',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.text', 'Mensaje desde app anterior');
    }

    public function test_non_participant_cannot_read_or_write_messages(): void
    {
        [$owner, $recipient, $outsider] = User::factory()->count(3)->create();
        $conversation = Conversation::create();
        $conversation->users()->attach([$owner->id, $recipient->id]);

        $this->actingAs($outsider)
            ->getJson("/api/v1/chat/{$conversation->id}")
            ->assertForbidden();

        $this->actingAs($outsider)
            ->postJson("/api/v1/chat/{$conversation->id}/messages", [
                'text' => 'No autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $outsider->id,
            'text' => 'No autorizado',
        ]);
    }
}
