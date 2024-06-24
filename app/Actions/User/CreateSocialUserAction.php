<?php

namespace App\Actions\User;

use App\Enums\SocialiteDriverEnum;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSocialUserAction
{
    use AsAction;

    public function handle(array $data, SocialiteDriverEnum $driver, mixed $socialUser): User
    {
        try {
            $user = CreateUserAction::run($data);

            $user->socialAccounts()
                ->updateOrCreate([
                    'provider' => $driver,
                    'provider_id' => $socialUser->getId()
                ], [
                    'provider_token' => $socialUser->token,
                    'provider_refresh_token' => $socialUser->refresh_token
                ]);

            return $user;
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                'email' => $exception->getMessage()
            ]);
        }
    }

}
