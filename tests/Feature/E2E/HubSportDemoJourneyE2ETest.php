<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use Database\Seeders\DemoFullShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * E2E HTTP del journey demo completo (API real in-process).
 * Cubre el contrato que la app Flutter consume en integration_test.
 */
class HubSportDemoJourneyE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('demo.enabled', true);
        Mail::fake();
        $this->seed(DemoFullShowcaseSeeder::class);
    }

    public function test_e2e_demo_login_feed_post_checkout_metrics_reports_path(): void
    {
        // 1) Demo login
        $login = $this->postJson('/api/v1/auth/demo-login')->assertOk();
        $token = $login->json('token');
        $this->assertNotEmpty($token);
        $userId = $login->json('data.id');
        $this->assertNotEmpty($userId);

        // 2) Me + feature flags store
        $me = $this->withToken($token)->getJson('/api/v1/user/me')->assertSuccessful();
        $payload = $me->json('data') ?? $me->json();
        $this->assertSame(config('demo.user_email'), $payload['email'] ?? null);
        $this->assertTrue((bool) ($payload['demo_preview'] ?? false));
        $this->assertTrue((bool) (($payload['feature_flags']['store'] ?? false)));

        // 3) Feed all + my_sport
        $this->withToken($token)->getJson('/api/v1/posts')->assertSuccessful();
        $this->withToken($token)->getJson('/api/v1/posts?my_sport=1')->assertSuccessful();

        // 4) Crear publicación Wave 6 fields
        $post = $this->withToken($token)->postJson('/api/v1/posts', [
            'title' => 'E2E post',
            'body' => 'Publicación E2E HubSport',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'post_type' => 'training',
            'location' => 'Pista E2E',
            'activity_url' => 'https://www.strava.com/activities/e2e',
        ])->assertSuccessful();
        $this->assertSame('training', $post->json('data.post_type'));
        $this->assertSame('Pista E2E', $post->json('data.location'));

        // 5) News filter
        $sportId = data_get($payload, 'profile.sport_id')
            ?? data_get($payload, 'profile.sport.id');
        if ($sportId) {
            $this->withToken($token)
                ->getJson('/api/v1/sport/news?sport_id='.$sportId)
                ->assertSuccessful();
        }

        // 6) Scout videos CRUD
        $video = $this->withToken($token)->postJson('/api/v1/user/me/videos', [
            'title' => 'E2E clip',
            'video_url' => 'https://example.test/e2e.mp4',
            'kind' => 'game',
        ])->assertCreated();
        $videoId = $video->json('data.id');
        $this->withToken($token)->getJson('/api/v1/user/me/videos')->assertSuccessful();
        $this->withToken($token)->deleteJson("/api/v1/user/me/videos/{$videoId}")->assertSuccessful();

        // 7) Showcase + checkout comisión 12%
        $showcase = $this->withToken($token)->getJson('/api/v1/demo/showcase')->assertOk();
        $this->assertNotEmpty($showcase->json('data.store_products'));

        $order = $this->withToken($token)->postJson('/api/v1/demo/store/checkout', [
            'product_id' => 'prd_demo_spikes',
            'variant' => '41',
            'recipient_name' => 'E2E Tester',
            'city' => 'Santo Domingo',
            'address' => 'Pista 1',
        ])->assertCreated()->json('data');

        $this->assertSame(12.0, (float) $order['commission_percent']);
        $this->assertEqualsWithDelta(10.68, (float) $order['commission_amount'], 0.001);

        // 8) Reports index: demo user normalmente 403 (no moderador)
        $reports = $this->withToken($token)->getJson('/api/v1/reports');
        $this->assertTrue(
            in_array($reports->status(), [200, 403], true),
            'Reports debe responder 200 o 403, got '.$reports->status()
        );

        // Usuario demo sigue existiendo
        $this->assertTrue(
            User::query()->where('email', config('demo.user_email'))->exists()
        );
    }
}
