<?php

namespace Tests\Feature\Flows;

use App\Mail\TutorMessageCopyMail;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class Wave7RemainingCodeFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_recruiter_pending_review_tutor_copy_reports_list_and_commission(): void
    {
        Mail::fake();

        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::factory()->create(['name' => 'Fútbol']);

        $this->postJson('/api/v1/auth/register', [
            'email' => 'recruiter@hubsport.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'profile' => [
                'first_name' => 'Rec',
                'last_name' => 'Ruiter',
                'country_id' => $country->id,
                'city' => 'SD',
                'sport_id' => $sport->id,
                'profile_type' => 'recruiter',
                'birth_date' => '1988-01-01',
            ],
        ])->assertStatus(422);

        $reg = $this->postJson('/api/v1/auth/register', [
            'email' => 'recruiter@hubsport.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'profile' => [
                'first_name' => 'Rec',
                'last_name' => 'Ruiter',
                'country_id' => $country->id,
                'city' => 'SD',
                'sport_id' => $sport->id,
                'profile_type' => 'recruiter',
                'birth_date' => '1988-01-01',
                'organization_name' => 'Academia Norte',
                'rnc' => '1-01-12345-6',
                'organization_document_url' => 'https://example.test/docs/rnc.pdf',
            ],
        ]);
        $reg->assertSuccessful();
        $recruiterId = $reg->json('data.id') ?? $reg->json('user.id');
        $recruiter = User::query()->findOrFail($recruiterId);
        $this->assertSame('pending_review', $recruiter->fresh()->profile->account_status);

        $this->asApiUser($recruiter)
            ->postJson('/api/v1/jobs', [
                'title' => 'No aún',
                'company' => 'Academia',
                'location' => 'SD',
                'contract_type' => 'full_time',
                'description' => 'Desc',
                'application_type' => 'simple',
            ])
            ->assertStatus(403);

        $minor = User::factory()->create(['name' => 'Minor Athlete']);
        Profile::query()->create([
            'user_id' => $minor->id,
            'first_name' => 'Minor',
            'last_name' => 'Athlete',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => now()->subYears(15)->toDateString(),
            'guardian_email' => 'tutor@hubsport.test',
        ]);

        $adult = User::factory()->create(['name' => 'Adult Coach']);
        Profile::query()->create([
            'user_id' => $adult->id,
            'first_name' => 'Adult',
            'last_name' => 'Coach',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'coach',
            'account_status' => 'active',
            'birth_date' => now()->subYears(28)->toDateString(),
        ]);

        $conversation = Conversation::query()->create([]);
        $conversation->users()->attach([$adult->id, $minor->id]);

        $this->asApiUser($adult)
            ->postJson('/api/v1/chat/'.$conversation->id.'/messages', [
                'text' => 'Hola, ¿entrenamos mañana?',
            ])
            ->assertCreated();

        Mail::assertSent(TutorMessageCopyMail::class, function (TutorMessageCopyMail $mail) {
            return $mail->hasTo('tutor@hubsport.test');
        });

        UserReport::query()->create([
            'reporter_id' => $adult->id,
            'target_user_id' => $minor->id,
            'reason' => 'fake_profile',
            'priority' => 'normal',
            'status' => 'open',
        ]);

        $moderator = User::factory()->create([
            'name' => 'Moderator',
            'email' => 'admin@hubsport.test',
        ]);
        Profile::query()->create([
            'user_id' => $moderator->id,
            'first_name' => 'Mod',
            'last_name' => 'Admin',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'fan',
            'account_status' => 'active',
            'birth_date' => '1990-01-01',
        ]);

        $this->asApiUser($adult)
            ->getJson('/api/v1/reports')
            ->assertForbidden();

        $this->asApiUser($moderator)
            ->getJson('/api/v1/reports')
            ->assertSuccessful()
            ->assertJsonPath('success', true);
        $this->assertNotEmpty($this->asApiUser($moderator)->getJson('/api/v1/reports')->json('data'));
    }
}
