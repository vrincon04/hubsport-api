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
        $user->update($request->safe()->all());
        $user->refresh();

        return $user;
    }
}
