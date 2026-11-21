<?php

namespace Tests\Feature\Flows;

use App\Mail\TutorMessageCopyMail;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\SportMetricDefinition;
use App\Models\User;
use Database\Seeders\DemoFullShowcaseSeeder;
use Database\Seeders\ScoutMetricDefinitionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

/**
 * Certificación cruzada de cambios Oleadas 6 y 7 (sin compra).
 */
class Wave67CertificationFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_wave6_invalid_post_type_and_activity_url_rejected(): void
    {
        [$athlete] = $this->seedAthlete();

        $this->asApiUser($athlete)
            ->postJson('/api/v1/posts', [
                'title' => 'Bad type',
                'body' => 'x',
                'status' => 'published',
                'published_at' => now()->format('Y-m-d H:i:s'),
                'post_type' => 'not_a_real_type',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['post_type']);

        $this->asApiUser($athlete)
            ->postJson('/api/v1/posts', [
                'title' => 'Bad url',
                'body' => 'x',
                'status' => 'published',
                'published_at' => now()->format('Y-m-d H:i:s'),
                'post_type' => 'training',
                'activity_url' => 'not-a-url',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['activity_url']);
    }

    public function test_wave6_federation_verify_ladder_and_coach_denied_federation(): void
    {
        [$athlete, $country, $sport] = $this->seedAthlete();
        $this->seed(ScoutMetricDefinitionsSeeder::class);

        $def = SportMetricDefinition::query()->where('sport_id', $sport->id)->firstOrFail();

        $this->asApiUser($athlete)
            ->putJson('/api/v1/user/me/metrics', [
                'metrics' => [
                    [
                        'definition_id' => $def->id,
                        'value_numeric' => 1.23,
                        'recorded_at' => '2026-11-01',
                        'source' => 'self_reported',
                    ],
                ],
            ])
            ->assertSuccessful();

        $metricId = $this->asApiUser($athlete)->getJson('/api/v1/user/me/metrics')->json('data.0.id');

        $coach = $this->makeUser('Coach', 'coach', $country, $sport);
        $this->asApiUser($coach)
            ->patchJson("/api/v1/user/{$athlete->id}/metrics/{$metricId}/verify", [
                'verification_status' => 'federation_verified',
            ])
            ->assertForbidden();

        $federation = $this->makeUser('Fed', 'federation', $country, $sport);
        $this->asApiUser($federation)
            ->patchJson("/api/v1/user/{$athlete->id}/metrics/{$metricId}/verify", [
                'verification_status' => 'federation_verified',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.verification_status', 'federation_verified');
    }

    public function test_wave6_video_create_list_and_delete_idempotent(): void
    {
        [$athlete] = $this->seedAthlete();

        $created = $this->asApiUser($athlete)
            ->postJson('/api/v1/user/me/videos', [
                'title' => 'Clip cert',
                'video_url' => 'https://example.test/cert.mp4',
                'kind' => 'game',
            ])
            ->assertCreated();

        $videoId = $created->json('data.id');
        $this->assertNotEmpty($videoId);

        $list = $this->asApiUser($athlete)->getJson('/api/v1/user/me/videos')->assertSuccessful();
        $ids = collect($list->json('data') ?? [])->pluck('id');
        $this->assertTrue($ids->contains($videoId));

        $this->asApiUser($athlete)
            ->deleteJson("/api/v1/user/me/videos/{$videoId}")
            ->assertSuccessful();

        $after = $this->asApiUser($athlete)->getJson('/api/v1/user/me/videos')->assertSuccessful();
        $idsAfter = collect($after->json('data') ?? [])->pluck('id');
        $this->assertFalse($idsAfter->contains($videoId));

        // Segundo delete: no debe romper (404 o success vacío aceptable)
        $second = $this->asApiUser($athlete)
            ->deleteJson("/api/v1/user/me/videos/{$videoId}");
        $this->assertTrue(
            in_array($second->status(), [200, 204, 404], true),
            'Segundo delete debe ser seguro, status='.$second->status()
        );
    }

    public function test_wave7_sponsor_pending_review_and_profile_org_fields(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::factory()->create(['name' => 'Atletismo']);

        $reg = $this->postJson('/api/v1/auth/register', [
            'email' => 'sponsor@hubsport.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'profile' => [
                'first_name' => 'Brand',
                'last_name' => 'Sponsor',
                'country_id' => $country->id,
                'city' => 'SD',
                'sport_id' => $sport->id,
                'profile_type' => 'sponsor',
                'birth_date' => '1985-05-05',
                'organization_name' => 'Marca Demo',
                'rnc' => '1-31-99999-1',
                'organization_document_url' => 'https://example.test/rnc.pdf',
                'organization_website' => 'https://marca.example',
            ],
        ])->assertSuccessful();

        $userId = $reg->json('data.id') ?? $reg->json('user.id');
        $user = User::query()->findOrFail($userId);
        $this->assertSame('pending_review', $user->fresh()->profile->account_status);
        $this->assertSame('Marca Demo', $user->profile->organization_name);
        $this->assertSame('1-31-99999-1', $user->profile->rnc);

        $me = $this->asApiUser($user)->getJson('/api/v1/user/me')->assertSuccessful();
        $payload = $me->json('data') ?? $me->json();
        $profile = $payload['profile'] ?? $payload;
        $this->assertSame('Marca Demo', $profile['organization_name'] ?? null);
        $this->assertSame('1-31-99999-1', $profile['rnc'] ?? null);
    }

    public function test_wave7_tutor_copy_not_sent_between_adults(): void
    {
        Mail::fake();
        [$adultA, $country, $sport] = $this->seedAthlete('Adult A', '2000-01-01');
        $adultB = $this->makeUser('Adult B', 'coach', $country, $sport, '1990-01-01');

        $conversation = Conversation::query()->create([]);
        $conversation->users()->attach([$adultA->id, $adultB->id]);

        $this->asApiUser($adultA)
            ->postJson('/api/v1/chat/'.$conversation->id.'/messages', [
                'text' => 'Hola entre adultos',
            ])
            ->assertCreated();

        Mail::assertNotSent(TutorMessageCopyMail::class);
    }

    public function test_wave7_event_organizer_can_list_reports(): void
    {
        [$reporter, $country, $sport] = $this->seedAthlete();
        $target = $this->makeUser('Target', 'athlete', $country, $sport);

        $this->asApiUser($reporter)
            ->postJson('/api/v1/reports', [
                'target_user_id' => $target->id,
                'reason' => 'fake_profile',
                'details' => 'Mensajes repetidos',
            ])
            ->assertCreated();

        $organizer = $this->makeUser('Org', 'event_organizer', $country, $sport);

        $this->asApiUser($organizer)
            ->getJson('/api/v1/reports')
            ->assertSuccessful()
            ->assertJsonPath('success', true);

        $this->assertNotEmpty(
            $this->asApiUser($organizer)->getJson('/api/v1/reports')->json('data')
        );
    }

    public function test_wave7_checkout_commission_math_is_twelve_percent(): void
    {
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);

        $user = User::query()->where('email', config('demo.user_email'))->firstOrFail();
        $token = $user->createToken('cert', ['demo'])->plainTextToken;

        $order = $this->withToken($token)
            ->postJson('/api/v1/demo/store/checkout', [
                'product_id' => 'prd_demo_spikes',
                'variant' => '41',
                'recipient_name' => 'Cert Tester',
                'city' => 'Santo Domingo',
                'address' => 'Calle 1',
            ])
            ->assertCreated()
            ->json('data');

        $amount = (float) $order['amount'];
        $this->assertSame(89.0, $amount);
        $this->assertSame(12.0, (float) $order['commission_percent']);
        $this->assertEqualsWithDelta(10.68, (float) $order['commission_amount'], 0.001);
        $this->assertEqualsWithDelta(78.32, (float) $order['net_amount'], 0.001);
        $this->assertEqualsWithDelta(
            $amount - (float) $order['commission_amount'],
            (float) $order['net_amount'],
            0.001
        );
    }

    /**
     * @return array{0: User, 1: Country, 2: Sport}
     */
    private function seedAthlete(string $name = 'Athlete Cert', string $birth = '2000-01-01'): array
    {
        $country = Country::query()->first()
            ?? Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::query()->where('name', 'béisbol')->first()
            ?? Sport::factory()->create(['name' => 'béisbol']);

        $user = $this->makeUser($name, 'athlete', $country, $sport, $birth);

        return [$user, $country, $sport];
    }

    private function makeUser(
        string $name,
        string $profileType,
        Country $country,
        Sport $sport,
        string $birth = '1990-01-01',
    ): User {
        $user = User::factory()->create(['name' => $name]);
        Profile::query()->create([
            'user_id' => $user->id,
            'first_name' => explode(' ', $name)[0],
            'last_name' => explode(' ', $name.' X')[1] ?? 'User',
            'country_id' => $country->id,
            'city' => 'SD',
            'sport_id' => $sport->id,
            'profile_type' => $profileType,
            'account_status' => 'active',
            'birth_date' => $birth,
        ]);

        return $user->fresh(['profile']);
    }
}
