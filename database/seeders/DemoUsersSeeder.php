<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cuentas fijas para emulador / QA. Contraseña: password
 *
 * demo@hubsport.test  → Usuario Demo
 * prueba@hubsport.test → Usuario Prueba
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $countryId = Country::query()->value('id');
        $sportId = Sport::query()->value('id');

        if (! $countryId || ! $sportId) {
            $this->command?->warn('DemoUsersSeeder: sin país o deporte; ejecuta seeders base antes.');

            return;
        }

        $accounts = [
            ['email' => 'demo@hubsport.test', 'name' => 'Usuario Demo', 'first_name' => 'Demo', 'last_name' => 'Hubsport'],
            ['email' => 'prueba@hubsport.test', 'name' => 'Usuario Prueba', 'first_name' => 'Prueba', 'last_name' => 'Sport'],
        ];

        foreach ($accounts as $row) {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if ($user->profile) {
                continue;
            }

            $profile = new Profile;
            $profile->user_id = $user->id;
            $profile->country_id = $countryId;
            $profile->sport_id = $sportId;
            $profile->first_name = $row['first_name'];
            $profile->last_name = $row['last_name'];
            $profile->bio = 'Cuenta de prueba';
            $profile->birth_date = '1990-01-15';
            $profile->save();
        }
    }
}
