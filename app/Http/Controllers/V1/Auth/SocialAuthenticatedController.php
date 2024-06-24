<?php

namespace App\Http\Controllers\V1\Auth;

use App\Actions\User\CreateSocialUserAction;
use App\Enums\SocialiteDriverEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthSocialRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthenticatedController extends Controller
{
    public function __invoke(SocialiteDriverEnum $driver, AuthSocialRequest $request)
    {
        try {
            $socialUser = $driver->getDriver()
                ->stateless()
                ->userFromToken($request->input('access_token'));

            $user = CreateSocialUserAction::run($driver->getData($socialUser), $driver, $socialUser);

            Auth::login($user);

            $user->load('profile');

            return response()->json([
                'message' => 'User authenticated successfully',
                'data' => UserResource::make($user),
                'token' => $user->createToken(config('app.name'))->plainTextToken,
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                $driver->value => $exception->getMessage()
            ]);
        }
    }
}
