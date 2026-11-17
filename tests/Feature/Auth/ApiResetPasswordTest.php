<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_resetting_password_revokes_existing_api_tokens(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.test']);
        $token = $user->createToken('compromised');
        EmailVerification::query()->create([
            'email' => $user->email,
            'code' => '123456',
        ]);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'code' => '123456',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
