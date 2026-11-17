<?php

namespace Tests\Feature\Flows;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class JobsFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_create_list_apply_and_save_job_flow(): void
    {
        $recruiter = User::factory()->create(['name' => 'Recruiter Flow']);
        $athlete = User::factory()->create(['name' => 'Athlete Flow']);

        $create = $this->asApiUser($recruiter)->postJson('/api/v1/jobs', [
            'title' => 'Entrenador juvenil',
            'company' => 'Club Horizonte',
            'location' => 'Santiago, DO',
            'contract_type' => 'Jornada completa',
            'description' => 'Buscamos coach con experiencia en categorías juveniles.',
            'application_type' => 'simple',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.title', 'Entrenador juvenil');

        $jobId = $create->json('data.id');
        $this->assertNotEmpty($jobId);

        $this->asApiUser($athlete)
            ->getJson('/api/v1/jobs')
            ->assertOk()
            ->assertJsonFragment(['id' => $jobId]);

        $this->asApiUser($athlete)
            ->getJson("/api/v1/jobs/{$jobId}")
            ->assertOk()
            ->assertJsonPath('data.id', $jobId);

        $this->asApiUser($athlete)
            ->postJson("/api/v1/jobs/{$jobId}/apply", [
                'message' => 'Me interesa el puesto.',
            ])
            ->assertCreated();

        $this->asApiUser($athlete)
            ->getJson('/api/v1/jobs/my-applications')
            ->assertOk();

        $this->asApiUser($athlete)
            ->postJson("/api/v1/jobs/{$jobId}/save")
            ->assertSuccessful();

        $this->asApiUser($athlete)
            ->getJson('/api/v1/jobs/my-saved')
            ->assertOk();

        $this->assertDatabaseHas('job_offers', [
            'id' => $jobId,
            'user_id' => $recruiter->id,
        ]);

        $this->assertTrue(JobOffer::query()->whereKey($jobId)->exists());
    }

    public function test_owner_cannot_apply_to_own_job(): void
    {
        $owner = User::factory()->create();
        $job = JobOffer::factory()->create(['user_id' => $owner->id]);

        $this->asApiUser($owner)
            ->postJson("/api/v1/jobs/{$job->id}/apply")
            ->assertForbidden();
    }
}
