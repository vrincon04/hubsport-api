<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DemoLoginController extends Controller
{
    public function __invoke(): JsonResponse
    {
        abort_unless(config('demo.enabled'), Response::HTTP_NOT_FOUND);

        $user = User::query()
            ->where('email', config('demo.user_email'))
            ->with(['profile', 'avatar'])
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'El usuario demo no está disponible.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $expiration = now()->addMinutes(max(1, (int) config('demo.token_expiration_minutes', 120)));
        $user->tokens()->where('name', 'demo-preview')->delete();
        $token = $user->createToken('demo-preview', ['demo'], $expiration);

        return response()->json([
            'message' => 'Sesión demo iniciada correctamente.',
            'data' => new UserResource($user),
            'token' => $token->plainTextToken,
        ]);
    }
}
