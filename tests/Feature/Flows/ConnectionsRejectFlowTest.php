<?php

namespace Tests\Feature\Flows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ConnectionsRejectFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_reject_incoming_connection_request_flow(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $request = $this->asApiUser($sender)->postJson('/api/v1/connections', [
            'connected_user_id' => $recipient->id,
        ])->assertCreated();

        $connectionId = $request->json('data.id');

        $this->asApiUser($recipient)
            ->postJson("/api/v1/connections/{$connectionId}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->asApiUser($recipient)
            ->getJson('/api/v1/connections/requests/incoming')
            ->assertOk()
            ->assertJsonPath('data', []);
    }
}
