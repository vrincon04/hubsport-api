<?php

namespace App\Http\Controllers\V1\User;

use App\Actions\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class UpdateController extends Controller
{
    public function __invoke(UpdateUserRequest $request, User $user)
    {
        $user = UpdateUserAction::run($request, $user);

        return new UserResource($user);
    }
}
