<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUserAction
{
    use AsAction;

    /**
     * Execute the action to create a new user with a profile.
     *
     * @param array $data
     * @return User
     */
    public function handle(array $data): User
    {
        $profile = $data['profile'];
        $name = $data['name'] ?? trim("{$profile['first_name']} {$profile['last_name']}");

        $user = User::firstOrCreate([
            'email' => $data['email'],
        ], [
            'name' => $name,
            'password' => $data['password'],
        ]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profile);

        return $user->load(['profile.country', 'profile.sport', 'avatar']);
    }
}
