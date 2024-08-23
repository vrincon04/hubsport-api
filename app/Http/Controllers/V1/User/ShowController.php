<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;

class ShowController extends Controller
{
    public function __invoke(User $user)
    {
        $user->load(['profile', 'avatar']);

        return new UserResource($user);
    }
}
