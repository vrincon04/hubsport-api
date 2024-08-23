<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class MeController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $user->load(['profile', 'avatar']);

        return new UserResource($user);
    }
}
