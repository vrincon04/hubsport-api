<?php

namespace Tests\Feature\Flows;

use App\Mail\GuardianConsentMail;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class MinorsSafetyFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_age_guardian_permissions_reports_and_scam_warning_flow(): void
    {
        Mail::fake();

        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::factory()->create(['name' => 'Fútbol']);

        $this->postJson('/api/v1/auth/register', [
            'email' => 'kid11@hubsport.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'profile' => [
                'first_name' => 'Kid',
                'last_name' => 'Once',
                'country_id' => $country->id,
                'city' => 'Santiago',
                'sport_id' => $sport->id,
                'profile_type' => 'athlete',
                'birth_date' => now()->subYears(11)->toDateString(),
            ],
        ])->assertStatus(422)->assertJsonValidationErrors(['profile.birth_date']);

        $this->postJson('/api/v1/auth/register', [
            'email' => 'teen@hubsport.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'profile' => [
                'first_name' => 'Teen',
                'last_name' => 'Athlete',
                'country_id' => $country->id,
                'city' => 'Santiago',
                'sport_id' => $sport->id,
                'profile_type' => 'athlete',
                'birth_date' => now()->subYears(15)->toDateString(),
            ],
        ])->assertStatus(422)->assertJsonValidationErrors(['profile.guardian_email']);

        $register = $this->postJson('/api/v1/auth/register', [
            'email' => 'teen@hubsport.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'profile' => [
                'first_name' => 'Teen',
                'last_name' => 'Athlete',
                'country_id' => $country->id,
                'city' => 'Santiago',
                'sport_id' => $sport->id,
                'profile_type' => 'athlete',
                'birth_date' => now()->subYears(15)->toDateString(),
                'guardian_email' => 'tutor@hubsport.test',
                'phone_number' => '+18095550111',
                'current_team' => 'Academia Sur',
            ],
        ]);

        $register->assertSuccessful()
            ->assertJsonPath('data.profile.account_status', 'pending_guardian');

        Mail::assertSent(GuardianConsentMail::class);

        $teen = User::query()->where('email', 'teen@hubsport.test')->firstOrFail();
        $token = $teen->profile->guardian_token;
        $this->assertNotEmpty($token);

        $this->asApiUser($teen)
            ->postJson('/api/v1/jobs', [
                'title' => 'Scout role',
                'company' => 'Club',
                'location' => 'SD',
                'contract_type' => 'full_time',
                'description' => 'Desc',
                'application_type' => 'simple',
            ])
            ->assertStatus(403);

        $this->getJson('/api/v1/auth/guardian/confirm?token='.$token)
            ->assertSuccessful()
            ->assertJsonPath('data.account_status', 'active');

        $teen->refresh()->load('profile');
        $this->assertSame('active', $teen->profile->account_status);

        $athlete = User::factory()->create(['name' => 'Adult Athlete']);
        Profile::query()->create([
            'user_id' => $athlete->id,
            'first_name' => 'Adult',
            'last_name' => 'Athlete',
            'country_id' => $country->id,
            'city' => 'La Vega',
            'sport_id' => $sport->id,
            'profile_type' => 'athlete',
            'account_status' => 'active',
            'birth_date' => now()->subYears(20)->toDateString(),
        ]);

        $this->asApiUser($athlete)
            ->postJson('/api/v1/jobs', [
                'title' => 'No debería',
                'company' => 'X',
                'location' => 'SD',
                'contract_type' => 'full_time',
                'description' => 'Desc',
                'application_type' => 'simple',
            ])
            ->assertStatus(403);

        $recruiter = User::factory()->create(['name' => 'Recruiter']);
        Profile::query()->create([
            'user_id' => $recruiter->id,
            'first_name' => 'Rec',
            'last_name' => 'Ruiter',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'recruiter',
            'account_status' => 'active',
            'birth_date' => now()->subYears(30)->toDateString(),
            'verified_badge' => true,
        ]);

        $this->asApiUser($recruiter)
            ->postJson('/api/v1/jobs', [
                'title' => 'Atleta juvenil',
                'company' => 'Academia',
                'location' => 'SD',
                'contract_type' => 'full_time',
                'description' => 'Desc',
                'application_type' => 'simple',
            ])
            ->assertCreated();

        $stranger = User::factory()->create(['name' => 'Stranger']);
        Profile::query()->create([
            'user_id' => $stranger->id,
            'first_name' => 'Str',
            'last_name' => 'Anger',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => 'fan',
            'account_status' => 'active',
            'birth_date' => now()->subYears(25)->toDateString(),
        ]);

        $this->asApiUser($stranger)
            ->getJson('/api/v1/user/'.$teen->id)
            ->assertSuccessful()
            ->assertJsonPath('data.profile.phone_number', null)
            ->assertJsonPath('data.profile.current_team', null);

        $this->asApiUser($stranger)
            ->postJson('/api/v1/reports', [
                'target_user_id' => $teen->id,
                'reason' => 'inappropriate_minor',
                'details' => 'Contacto inapropiado',
            ])
            ->assertCreated()
            ->assertJsonPath('meta.priority', 'immediate');

        $this->assertSame(1, UserReport::query()->where('priority', 'immediate')->count());
    }
}
