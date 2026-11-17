<?php

namespace Tests\Feature\Flows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ConnectionsFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_request_accept_list_and_remove_connection_flow(): void
    {
        $sender = User::factory()->create(['name' => 'Sender Flow']);
        $recipient = User::factory()->create(['name' => 'Recipient Flow']);

        $request = $this->asApiUser($sender)->postJson('/api/v1/connections', [
            'connected_user_id' => $recipient->id,
        ]);

        $request->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $connectionId = $request->json('data.id');
        $this->assertNotEmpty($connectionId);

        $this->assertDatabaseHas('connections', [
            'id' => $connectionId,
            'user_id' => $sender->id,
            'connected_user_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $incoming = $this->asApiUser($recipient)
            ->getJson('/api/v1/connections/requests/incoming')
            ->assertOk();

        $incomingIds = collect($incoming->json('data'))->pluck('id');
        $this->assertTrue(
            $incomingIds->contains($connectionId),
            'Incoming should include connection '.$connectionId.' got '.$incomingIds->implode(',')
        );

        $this->asApiUser($sender)
            ->getJson('/api/v1/connections/requests/outgoing')
            ->assertOk()
            ->assertJsonFragment(['id' => $connectionId]);

        $this->asApiUser($recipient)
            ->postJson("/api/v1/connections/{$connectionId}/accept")
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->asApiUser($sender)
            ->getJson('/api/v1/connections')
            ->assertOk()
            ->assertJsonFragment(['id' => $connectionId]);

        $this->asApiUser($sender)
            ->deleteJson("/api/v1/connections/{$connectionId}")
            ->assertOk();

        $this->assertDatabaseMissing('connections', ['id' => $connectionId]);
    }

    public function test_cannot_connect_to_self(): void
    {
        $user = User::factory()->create();

        $this->asApiUser($user)
            ->postJson('/api/v1/connections', [
                'connected_user_id' => $user->id,
            ])
            ->assertStatus(422);
    }
}
