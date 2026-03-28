<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\EmailVerification;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request)
    {
        // Generate a 6-digit OTP
        $code = rand(100000, 999999);
        
        EmailVerification::updateOrCreate(
            ['email' => $request->email],
            ['code' => (string) $code]
        );

        // TODO: In a production environment, send an email to $request->email with $code.
        // For example: Mail::to($request->email)->send(new ResetPasswordMail($code));

        return response()->json([
            'message' => 'Verification code sent to your email address.',
            // Return code for local testing and debugging purposes
            'code' => config('app.env') === 'local' ? (string) $code : null, 
        ], Response::HTTP_OK);
    }
}
