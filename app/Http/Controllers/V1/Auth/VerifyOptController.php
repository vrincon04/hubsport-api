<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OptVerificationRequest;
use App\Models\EmailVerification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class VerifyOptController extends Controller
{
    public function __invoke(OptVerificationRequest $request)
    {
        try {
            $verify = EmailVerification::where('code', $request->code)->firstOrFail();

            $verify->delete();
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([
                'code' => 'The OPT verification code is invalid.',
            ]);
        }

        return response()->json([
            'message' => 'OPT Verified Successfully',
        ], Response::HTTP_OK);
    }
}
