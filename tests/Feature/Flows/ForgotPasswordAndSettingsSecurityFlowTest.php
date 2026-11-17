<?php

namespace Tests\Feature\Flows;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ForgotPasswordAndSettingsSecurityFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_forgot_password_stores_verification_code(): void
    {
        $user = User::factory()->create(['email' => 'forgot.flow@hubsport.test']);

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ])->assertOk();

        $this->assertDatabaseHas('email_verifications', [
            'email' => $user->email,
        ]);
        $this->assertNotEmpty(
            EmailVerification::query()->where('email', $user->email)->value('code')
        );
    }

    public function test_change_password_and_sessions_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'secure.flow@hubsport.test',
            'password' => Hash::make('Password1'),
        ]);

        $this->asApiUser($user)
            ->postJson('/api/v1/user/settings/change-password', [
                'current_password' => 'Password1',
                'password' => 'Password2',
                'password_confirmation' => 'Password2',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('Password2', $user->fresh()->password));

        DB::table('sessions')->insert([
            'id' => 'session-flow-1',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'x',
            'last_activity' => time(),
        ]);

        $this->asApiUser($user)
            ->getJson('/api/v1/user/settings/sessions')
            ->assertOk()
            ->assertJsonFragment(['id' => 'session-flow-1']);

        $this->asApiUser($user)
            ->deleteJson('/api/v1/user/settings/sessions/session-flow-1')
            ->assertOk();

        $this->assertDatabaseMissing('sessions', ['id' => 'session-flow-1']);
    }
}
