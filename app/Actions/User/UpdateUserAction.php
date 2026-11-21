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

        $profileData = $request->validated('profile');
        if (is_array($profileData)) {
            unset(
                $profileData['user_id'],
                $profileData['verified_badge'],
                $profileData['verification_status'],
                $profileData['verified_at'],
                $profileData['sport_locked_at'],
                $profileData['phone_verified_at'],
            );

            $existing = $user->profile;
            if ($existing?->sport_id) {
                unset($profileData['sport_id']);
            } elseif (! empty($profileData['sport_id'])) {
                $profileData['sport_locked_at'] = now();
            }

            $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);
        }
        $user->refresh()->load(['profile.country', 'profile.sport', 'avatar']);

        return $user;
    }
}
