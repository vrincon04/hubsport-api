<?php

namespace Tests\Feature\Flows;

use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithApiAuth;
use Tests\TestCase;

class ContactAndProfileUpdateFlowTest extends TestCase
{
    use InteractsWithApiAuth;
    use RefreshDatabase;

    public function test_contact_and_profile_update_flow(): void
    {
        $country = Country::query()->create(['name' => 'República Dominicana', 'code' => 'DO']);
        $sport = Sport::factory()->create(['name' => 'Natación']);
        $user = User::factory()->create(['name' => 'Perfil Update']);

        Profile::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Perfil',
            'last_name' => 'Update',
            'country_id' => $country->id,
            'city' => 'La Vega',
            'sport_id' => $sport->id,
            'profile_type' => 'athlete',
        ]);

        $this->postJson('/api/v1/contact', [
            'name' => 'Usuario Soporte',
            'email' => 'soporte@hubsport.test',
            'subject' => 'Ayuda con la cuenta',
            'message' => 'Necesito ayuda con mi cuenta de HubSport.',
        ])->assertSuccessful();

        $this->asApiUser($user)
            ->putJson("/api/v1/user/{$user->id}", [
                'profile' => [
                    'first_name' => 'Nuevo',
                    'last_name' => 'Nombre',
                    'city' => 'Santiago',
                    'country_id' => $country->id,
                    'sport_id' => $sport->id,
                    'profile_type' => 'athlete',
                ],
            ])
            ->assertSuccessful();
    }
}
