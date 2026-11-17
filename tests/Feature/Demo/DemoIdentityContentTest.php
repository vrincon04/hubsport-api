<?php

namespace Tests\Feature\Demo;

use App\Models\User;
use Database\Seeders\DemoFullShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoIdentityContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('demo.enabled', true);
        $this->seed(DemoFullShowcaseSeeder::class);
    }

    public function test_demo_events_include_absolute_photo_urls(): void
    {
        $demo = User::query()->where('email', config('demo.user_email'))->firstOrFail();
        $token = $demo->createToken('identity-test', ['demo'])->plainTextToken;

        $photos = collect(
            $this->withToken($token)->getJson('/api/v1/events')->assertOk()->json('data')
        )->pluck('photo_url');

        $this->assertNotEmpty($photos);
        $this->assertTrue($photos->every(fn ($url) => is_string($url) && str_starts_with($url, 'https://')));
        $this->assertFalse($photos->contains(fn ($url) => str_contains($url, 'example.test')));
    }

    public function test_peer_profiles_include_career_collections(): void
    {
        $demo = User::query()->where('email', config('demo.user_email'))->firstOrFail();
        $peer = User::query()->where('email', 'demo.peer.athlete@hubsport.test')->firstOrFail();
        $token = $demo->createToken('identity-test', ['demo'])->plainTextToken;

        $profile = $this->withToken($token)
            ->getJson('/api/v1/user/'.$peer->id)
            ->assertOk()
            ->json('data.profile');

        $this->assertNotEmpty($profile['bio']);
        $this->assertNotEmpty($profile['experience']);
        $this->assertNotEmpty($profile['achievements']);
        $this->assertNotEmpty($profile['education']);
        $this->assertStringNotContainsString('celebridad', strtolower((string) $profile['bio']));
    }

    public function test_demo_references_include_actionable_states(): void
    {
        $demo = User::query()->where('email', config('demo.user_email'))->firstOrFail();
        $token = $demo->createToken('identity-test', ['demo'])->plainTextToken;

        $inbox = $this->withToken($token)
            ->getJson('/api/v1/references')
            ->assertOk()
            ->json('data');

        $receivedStatuses = collect($inbox['received'])->pluck('status');
        $sentStatuses = collect($inbox['sent'])->pluck('status');

        $this->assertTrue($receivedStatuses->contains('pending'));
        $this->assertTrue($receivedStatuses->contains('accepted'));
        $this->assertTrue($sentStatuses->contains('pending_confirmation'));
    }
}
