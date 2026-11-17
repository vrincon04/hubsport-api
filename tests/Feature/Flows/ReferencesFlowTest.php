<?php

namespace Tests\Feature\Flows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ReferencesFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_request_accept_write_and_list_references_flow(): void
    {
        $requester = User::factory()->create(['name' => 'Requester Ref']);
        $recipient = User::factory()->create(['name' => 'Recipient Ref']);

        $create = $this->asApiUser($requester)->postJson('/api/v1/references/requests', [
            'recipient_id' => $recipient->id,
            'relationship_type' => 'coach',
            'message' => '¿Me das una referencia?',
        ]);

        $create->assertCreated();
        $requestId = $create->json('data.id');
        $this->assertNotEmpty($requestId);

        $this->asApiUser($recipient)
            ->postJson("/api/v1/references/requests/{$requestId}/accept")
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $body = str_repeat('Excelente profesional deportivo. ', 3);
        $this->asApiUser($recipient)
            ->postJson("/api/v1/references/requests/{$requestId}/write", [
                'body' => $body,
            ])
            ->assertSuccessful();

        $this->asApiUser($requester)
            ->getJson('/api/v1/references')
            ->assertOk()
            ->assertJsonStructure(['data' => ['received', 'sent']]);

        $this->asApiUser($requester)
            ->getJson("/api/v1/references/users/{$requester->id}")
            ->assertOk();
    }

    public function test_reject_reference_request_flow(): void
    {
        $requester = User::factory()->create();
        $recipient = User::factory()->create();

        $requestId = $this->asApiUser($requester)->postJson('/api/v1/references/requests', [
            'recipient_id' => $recipient->id,
            'relationship_type' => 'teammate',
        ])->assertCreated()->json('data.id');

        $this->asApiUser($recipient)
            ->postJson("/api/v1/references/requests/{$requestId}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');
    }
}
