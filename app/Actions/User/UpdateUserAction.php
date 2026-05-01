<?php

namespace App\Actions\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUserAction
{
    use AsAction;

    /**
     * Execute the action to create a new user with a profile.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return User
     */
    public function handle(UpdateUserRequest $request, User $user): User
    {
        $safe = $request->safe()->all();
        $user->update(collect($safe)->except('profile')->all());

        if ($request->has('profile')) {
            $user->profile()->updateOrCreate(['user_id' => $user->id], $request->input('profile'));
        }
        $user->refresh()->load(['profile.country', 'profile.sport', 'avatar']);

        return $user;
    }
}
