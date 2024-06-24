<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $user = $request->authenticate();

        $user->load('profile');

        return response()->json([
            'message' => 'User authenticated successfully',
            'data' => UserResource::make($user),
            'token' => $user->createToken(config('app.name'))->plainTextToken,
        ], Response::HTTP_OK);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ], Response::HTTP_OK);
    }
}
