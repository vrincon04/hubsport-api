<?php

namespace App\Http\Controllers\V1\Meta;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    /**
     * Accept contact form submissions (wire email/CRM later).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
            'email' => $user
                ? ['nullable', 'email', 'max:255']
                : ['required', 'email', 'max:255'],
        ]);

        Log::info('contact_form', [
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'email' => $validated['email'] ?? $user?->email,
            'user_id' => $user?->id,
        ]);

        return response()->json([
            'message' => 'Thank you. We have received your message.',
        ], Response::HTTP_ACCEPTED);
    }
}
