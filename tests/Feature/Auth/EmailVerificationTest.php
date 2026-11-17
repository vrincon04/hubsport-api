<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

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
