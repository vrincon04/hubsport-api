<?php

namespace App\Enums;

use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

enum SocialiteDriverEnum: string
{
    case APPLE = 'apple';
    case FACEBOOK = 'facebook';
    case GOOGLE = 'google';

    /**
     * Get the Socialite provider.
     *
     * @return Provider
     */
    public function getDriver(): Provider
    {
        return match ($this) {
            self::APPLE => Socialite::driver(self::APPLE->value),
            self::FACEBOOK => Socialite::driver(self::FACEBOOK->value)->fields([
                'name',
                'first_name',
                'last_name',
                'email',
                'gender',
                'verified'
            ]),
            self::GOOGLE => Socialite::driver(self::GOOGLE->value),
        };
    }

    /**
     * Get the user data.
     *
     * @param User $user
     * @return array
     */
    public function getData(User $user): array
    {
        return match ($this) {
            self::APPLE => [
                'email' => $user->email,
                'password' => Str::random(20),
                'profile' => [
                    'first_name' => $user->user['name'],
                    'last_name' => ''
                ]
            ],
            self::FACEBOOK => [
                'email' => $user->getEmail(),
                'password' => Str::random(20),
                'profile' => [
                    'first_name' => $user->user['first_name'],
                    'last_name' => $user->user['last_name']
                ]
            ],
            self::GOOGLE => [
                'email' => $user->getEmail(),
                'password' => Str::random(20),
                'profile' => [
                    'first_name' => $user->user['given_name'],
                    'last_name' => $user->user['family_name']
                ]
            ],
        };
    }
}
