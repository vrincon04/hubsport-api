<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(config('app.frontend_url').'/dashboard?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_opt_verification_marks_user_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerification::create([
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response = $this->postJson('/api/v1/auth/verify/opt', [
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'OPT Verified Successfully');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertDatabaseMissing('email_verifications', [
            'email' => $user->email,
            'code' => '123456',
        ]);
    }

    public function test_opt_verification_does_not_verify_a_different_email(): void
    {
        $user = User::factory()->unverified()->create();
        $otherUser = User::factory()->unverified()->create();
        EmailVerification::create([
            'email' => $user->email,
            'code' => '123456',
        ]);

        $this->postJson('/api/v1/auth/verify/opt', [
            'email' => $otherUser->email,
            'code' => '123456',
        ])->assertUnprocessable();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertFalse($otherUser->fresh()->hasVerifiedEmail());
    }

    public function test_opt_verification_rejects_expired_codes(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerification::query()->insert([
            'email' => $user->email,
            'code' => '123456',
            'created_at' => now()->subMinutes(16),
            'updated_at' => now()->subMinutes(16),
        ]);

        $this->postJson('/api/v1/auth/verify/opt', [
            'email' => $user->email,
            'code' => '123456',
        ])->assertUnprocessable();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
