<?php

namespace Tests\Feature\Flows;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class JobsCancelUnsaveFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_cancel_application_unsave_and_applicants_flow(): void
    {
        $recruiter = User::factory()->create();
        $athlete = User::factory()->create();
        $job = JobOffer::factory()->create(['user_id' => $recruiter->id]);

        $this->asApiUser($athlete)
            ->postJson("/api/v1/jobs/{$job->id}/apply", ['message' => 'Hola'])
            ->assertCreated();

        $this->asApiUser($athlete)
            ->postJson("/api/v1/jobs/{$job->id}/save")
            ->assertOk();

        $this->asApiUser($recruiter)
            ->getJson("/api/v1/jobs/{$job->id}/applicants")
            ->assertOk()
            ->assertJsonStructure(['data']);

        $this->asApiUser($athlete)
            ->deleteJson("/api/v1/jobs/{$job->id}/apply")
            ->assertOk();

        $this->asApiUser($athlete)
            ->deleteJson("/api/v1/jobs/{$job->id}/unsave")
            ->assertOk();

        $this->asApiUser($recruiter)
            ->getJson('/api/v1/jobs/my-offers')
            ->assertOk();
    }
}
