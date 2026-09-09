<?php

namespace Tests\Feature\Demo;

use App\Models\User;
use Database\Seeders\DemoFullShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_login_is_hidden_when_disabled(): void
    {
        config()->set('demo.enabled', false);

        $this->postJson('/api/v1/auth/demo-login')->assertNotFound();
    }

    public function test_demo_login_issues_demo_scoped_token_and_flags(): void
    {
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);

        $response = $this->postJson('/api/v1/auth/demo-login')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'profile', 'avatar', 'demo_preview', 'feature_flags'],
                'token',
            ])
            ->assertJsonPath('data.demo_preview', true)
            ->assertJsonPath('data.feature_flags.billing', true)
            ->assertJsonPath('data.feature_flags.events', true);

        $plainTextToken = $response->json('token');
        $token = User::where('email', config('demo.user_email'))->firstOrFail()
            ->tokens()
            ->where('token', hash('sha256', explode('|', $plainTextToken, 2)[1]))
            ->firstOrFail();

        $this->assertSame(['demo'], $token->abilities);
        $this->assertNotNull($token->expires_at);
    }

    public function test_showcase_requires_demo_ability_and_contains_only_masked_payment_data(): void
    {
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);

        $normalUser = User::factory()->create();
        $normalToken = $normalUser->createToken('normal', ['*'])->plainTextToken;

        $this->withToken($normalToken)
            ->getJson('/api/v1/demo/showcase')
            ->assertForbidden();
        app('auth')->forgetGuards();

        $demoUser = User::where('email', config('demo.user_email'))->firstOrFail();
        $demoToken = $demoUser->createToken('demo-test', ['demo'])->plainTextToken;

        $response = $this->withToken($demoToken)
            ->getJson('/api/v1/demo/showcase')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'billing' => ['subscription', 'payment_methods', 'invoices'],
                    'groups',
                    'collaborations',
                    'store_products',
                ],
            ]);

        $payload = json_encode($response->json(), JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('cvv', strtolower($payload));
        $this->assertDoesNotMatchRegularExpression('/\b\d{13,19}\b/', $payload);
    }

    public function test_seeder_and_reset_are_idempotent_and_preserve_normal_users(): void
    {
        config()->set('demo.enabled', true);
        $normalUser = User::factory()->create(['email' => 'normal@example.test']);

        $this->seed(DemoFullShowcaseSeeder::class);
        $firstCounts = $this->demoCounts();
        $this->seed(DemoFullShowcaseSeeder::class);

        $this->assertSame($firstCounts, $this->demoCounts());
        $this->assertDatabaseHas('users', [
            'id' => $normalUser->id,
            'email' => 'normal@example.test',
        ]);

        $this->artisan('demo:reset')->assertSuccessful();
        $afterFirstReset = $this->demoCounts();
        $this->artisan('demo:reset')->assertSuccessful();

        $this->assertSame($afterFirstReset, $this->demoCounts());
        $this->assertDatabaseHas('users', ['id' => $normalUser->id]);
    }

    public function test_demo_session_has_sufficient_data_in_all_client_flows(): void
    {
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);

        $token = $this->postJson('/api/v1/auth/demo-login')->assertOk()->json('token');
        $this->withToken($token);

        $me = $this->getJson('/api/v1/user/me')->assertOk()->json('data');
        $this->assertTrue($me['demo_preview']);
        $this->assertSame('Sofía', $me['profile']['first_name']);
        $this->assertNotEmpty($me['profile']['bio']);
        $this->assertNotEmpty($me['profile']['experience']);
        $this->assertNotEmpty($me['profile']['achievements']);

        $this->assertGreaterThanOrEqual(4, $this->listCount($this->getJson('/api/v1/posts')->assertOk()));
        $this->assertGreaterThanOrEqual(3, $this->listCount($this->getJson('/api/v1/connections')->assertOk()));
        $this->assertGreaterThanOrEqual(1, $this->listCount($this->getJson('/api/v1/connections/requests/incoming')->assertOk()));
        $this->assertGreaterThanOrEqual(1, $this->listCount($this->getJson('/api/v1/connections/requests/outgoing')->assertOk()));

        $conversations = $this->getJson('/api/v1/chat')->assertOk()->json();
        $this->assertIsArray($conversations);
        $this->assertGreaterThanOrEqual(3, count($conversations));
        $messages = $this->getJson('/api/v1/chat/'.$conversations[0]['id'])->assertOk()->json();
        $this->assertGreaterThanOrEqual(2, count($messages));

        $this->assertGreaterThanOrEqual(2, $this->listCount($this->getJson('/api/v1/jobs')->assertOk()));
        $this->assertGreaterThanOrEqual(1, $this->listCount($this->getJson('/api/v1/jobs/my-applications')->assertOk()));
        $this->assertGreaterThanOrEqual(1, $this->listCount($this->getJson('/api/v1/jobs/my-saved')->assertOk()));
        $this->assertGreaterThanOrEqual(1, $this->listCount($this->getJson('/api/v1/jobs/my-offers')->assertOk()));

        $references = $this->getJson('/api/v1/references')->assertOk()->json('data');
        $this->assertGreaterThanOrEqual(1, count($references['received'] ?? []));
        $this->assertGreaterThanOrEqual(2, count($references['sent'] ?? []));
        $this->assertGreaterThanOrEqual(
            1,
            $this->listCount($this->getJson('/api/v1/references/users/'.$me['id'])->assertOk())
        );

        $this->assertGreaterThanOrEqual(2, $this->listCount($this->getJson('/api/v1/events')->assertOk()));
        $this->assertGreaterThanOrEqual(3, $this->listCount($this->getJson('/api/v1/sponsorships')->assertOk()));
        $this->assertNotEmpty($this->getJson('/api/v1/stories')->assertOk()->json('data'));

        $showcase = $this->getJson('/api/v1/demo/showcase')->assertOk()->json('data');
        $this->assertGreaterThanOrEqual(2, count($showcase['groups']));
        $this->assertGreaterThanOrEqual(2, count($showcase['collaborations']));
        $this->assertGreaterThanOrEqual(2, count($showcase['store_products']));
        $this->assertGreaterThanOrEqual(2, count($showcase['billing']['payment_methods']));
        $this->assertGreaterThanOrEqual(2, count($showcase['billing']['invoices']));
        $this->assertNotEmpty($showcase['billing']['subscription']['plan']);

        $this->getJson('/api/v1/user/settings')->assertOk()->assertJsonPath('data.privacy.publicProfile', true);
        $this->getJson('/api/v1/user/notifications')->assertOk();
    }

    public function test_demo_token_cannot_change_password(): void
    {
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);
        $demoUser = User::where('email', config('demo.user_email'))->firstOrFail();
        $token = $demoUser->createToken('demo-test', ['demo'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/user/settings/change-password', [
            'current_password' => 'password',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertForbidden();
    }

    public function test_demo_user_cannot_use_public_password_reset(): void
    {
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => config('demo.user_email'),
            'code' => '12345678',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertForbidden();
    }

    private function demoCounts(): array
    {
        $demoIds = User::query()
            ->where('email', config('demo.user_email'))
            ->orWhere('email', 'like', 'demo.peer.%@hubsport.test')
            ->pluck('id');

        return [
            'users' => $demoIds->count(),
            'posts' => DB::table('posts')->whereIn('user_id', $demoIds)->count(),
            'comments' => DB::table('comments')->whereIn('user_id', $demoIds)->count(),
            'connections' => DB::table('connections')->whereIn('user_id', $demoIds)->count(),
            'messages' => DB::table('messages')->whereIn('sender_id', $demoIds)->count(),
            'jobs' => DB::table('job_offers')->whereIn('user_id', $demoIds)->count(),
            'events' => DB::table('events')->whereIn('user_id', $demoIds)->count(),
            'sponsorships' => DB::table('sponsorships')->whereIn('user_id', $demoIds)->count(),
            'stories' => DB::table('stories')->whereIn('user_id', $demoIds)->count(),
            'notifications' => DB::table('notifications')->whereIn('notifiable_id', $demoIds)->count(),
        ];
    }

    private function listCount(\Illuminate\Testing\TestResponse $response): int
    {
        $payload = $response->json('data') ?? $response->json();
        if (is_array($payload) && array_is_list($payload)) {
            return count($payload);
        }
        if (is_array($payload) && isset($payload['data']) && is_array($payload['data'])) {
            return count($payload['data']);
        }

        return is_array($payload) ? count($payload) : 0;
    }
}
