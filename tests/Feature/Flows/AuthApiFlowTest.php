<?php

namespace Tests\Feature\Flows;

use App\Models\Country;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class AuthApiFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_register_me_logout_then_login_flow(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::factory()->create(['name' => 'Fútbol']);

        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana Pérez',
            'email' => 'ana.flow@hubsport.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'profile' => [
                'first_name' => 'Ana',
                'last_name' => 'Pérez',
                'country_id' => $country->id,
                'city' => 'Santo Domingo',
                'sport_id' => $sport->id,
                'profile_type' => 'athlete',
                'birth_date' => '1998-05-10',
            ],
        ]);

        $register->assertSuccessful()
            ->assertJsonPath('data.email', 'ana.flow@hubsport.test')
            ->assertJsonStructure(['token', 'data' => ['id', 'email']]);

        $token = $register->json('token');
        $userId = $register->json('data.id');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/v1/user/me')
            ->assertSuccessful()
            ->assertJsonPath('data.email', 'ana.flow@hubsport.test');

        $this->withToken($token)
            ->deleteJson('/api/v1/auth/logout')
            ->assertSuccessful();

        $this->flushHeaders();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'ana.flow@hubsport.test',
            'password' => 'Password1!',
        ]);

        $login->assertSuccessful()
            ->assertJsonStructure(['token', 'data' => ['id', 'email']]);

        $loginToken = $login->json('token');

        $this->withToken($loginToken)
            ->deleteJson('/api/v1/auth/logout')
            ->assertSuccessful();

        $this->assertSame(
            0,
            PersonalAccessToken::query()->where('tokenable_id', $userId)->count()
        );
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'locked@hubsport.test',
            'password' => bcrypt('Password1!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'locked@hubsport.test',
            'password' => 'WrongPass1!',
        ])->assertStatus(422);
    }
}
