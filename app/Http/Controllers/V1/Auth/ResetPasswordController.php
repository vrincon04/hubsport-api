<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;


class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): JsonResponse

    {
        abort_if(
            config('demo.enabled')
                && hash_equals((string) config('demo.user_email'), (string) $request->email),
            Response::HTTP_FORBIDDEN,
            'Esta acción no está disponible para el usuario demo.'
        );

        $verification = EmailVerification::where('email', $request->email)
                                        ->where('code', $request->code)
                                        ->first();

        if (!$verification) {
            throw ValidationException::withMessages([
                'code' => 'The provided code is invalid or has expired.',
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        $verification->delete();

        return response()->json([
            'message' => 'Password reset successfully.',
        ], Response::HTTP_OK);
    }
}
