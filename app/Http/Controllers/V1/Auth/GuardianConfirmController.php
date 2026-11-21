<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class GuardianConfirmController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->input('token') ?? $request->query('token');
        abort_unless(is_string($token) && strlen($token) >= 32, 422, 'Token inválido');

        $profile = Profile::query()
            ->where('guardian_token', $token)
            ->where('account_status', 'pending_guardian')
            ->first();

        abort_unless($profile, 404, 'Enlace inválido o ya utilizado');

        $profile->update([
            'account_status' => 'active',
            'guardian_confirmed_at' => now(),
            'guardian_token' => null,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'account_status' => 'active',
                'message' => 'Tutoría confirmada. La cuenta del menor ya está activa.',
            ],
            'error' => null,
        ]);
    }
}
