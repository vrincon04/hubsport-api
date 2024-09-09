<?php

namespace App\Http\Controllers\V1\Auth;

use App\Actions\User\CreateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegisteredController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        try {
            $user = CreateUserAction::run($request->safe()->all());

            //event(new Registered($user));

            Auth::login($user);

            return response()->json([
                'message' => 'User authenticated successfully',
                'data' => UserResource::make($user),
                'token' => $user->createToken(config('app.name'))->plainTextToken,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
