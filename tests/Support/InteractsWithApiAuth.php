<?php

namespace Tests\Support;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait InteractsWithApiAuth
{
    protected function apiToken(User $user, string $name = 'test'): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    protected function asApiUser(User $user)
    {
        Sanctum::actingAs($user);

        return $this;
    }
}
