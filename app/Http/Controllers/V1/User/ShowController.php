<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        abort_unless($user->isVisibleTo($request->user()), Response::HTTP_NOT_FOUND);

        $user->load(['profile.country', 'profile.sport', 'avatar']);

        return new UserResource($user);
    }
}
