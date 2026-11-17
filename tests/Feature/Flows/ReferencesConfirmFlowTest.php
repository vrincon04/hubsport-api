<?php

namespace Tests\Feature\Flows;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ReferencesConfirmFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_confirm_published_reference_flow(): void
    {
        $requester = User::factory()->create();
        $recipient = User::factory()->create();

        $requestId = $this->asApiUser($requester)->postJson('/api/v1/references/requests', [
            'recipient_id' => $recipient->id,
            'relationship_type' => 'coach',
        ])->assertCreated()->json('data.id');

        $this->asApiUser($recipient)
            ->postJson("/api/v1/references/requests/{$requestId}/accept")
            ->assertOk();

        $this->asApiUser($recipient)
            ->postJson("/api/v1/references/requests/{$requestId}/write", [
                'body' => str_repeat('Referencia profesional excelente. ', 2),
            ])
            ->assertCreated();

        $this->asApiUser($requester)
            ->postJson("/api/v1/references/requests/{$requestId}/confirm")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');

        $this->asApiUser($requester)
            ->getJson("/api/v1/references/users/{$requester->id}")
            ->assertOk()
            ->assertJsonFragment(['status' => 'published']);
    }
}
