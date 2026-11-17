<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OptVerificationRequest;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class VerifyOptController extends Controller
{
    public function __invoke(OptVerificationRequest $request)
    {
        try {
            DB::transaction(function () use ($request): void {
                $verify = EmailVerification::where('email', $request->email)
                    ->where('code', $request->code)
                    ->where('created_at', '>=', now()->subMinutes(15))
                    ->firstOrFail();

                User::where('email', $verify->email)
                    ->whereNull('email_verified_at')
                    ->update(['email_verified_at' => now()]);

                $verify->delete();
            });
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'code' => 'The OTP verification code is invalid or expired.',
            ]);
        }

        return response()->json([
            'message' => 'OPT Verified Successfully',
        ], Response::HTTP_OK);
    }
}
