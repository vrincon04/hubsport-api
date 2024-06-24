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
        $user = User::firstOrCreate(['email' => $data['email'], 'name' => $data['profile']['first_name']], $data);

        if (isset($data['profile'])) {
            $profileData = $data['profile'];
            $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);
        }

        return $user;
    }
}
