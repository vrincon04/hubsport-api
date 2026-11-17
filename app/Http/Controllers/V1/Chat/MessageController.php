<?php

namespace App\Http\Controllers\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public function index(Conversation $conversation): JsonResponse
    {
        if (! $this->userBelongsToConversation($conversation, request()->user()->id)) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $messages = $conversation->messages()->with('sender')->oldest()->get();

        return response()->json($messages);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        if (! $this->userBelongsToConversation($conversation, $request->user()->id)) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'text' => 'required_without:content|string|max:2000',
            'content' => 'required_without:text|string|max:2000',
        ]);
        $text = $validated['text'] ?? $validated['content'];

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'text' => $text,
        ]);

        return response()->json($message, 201);
    }

    private function userBelongsToConversation(Conversation $conversation, string $userId): bool
    {
        return $conversation->users()
            ->where('users.id', $userId)
            ->exists();
    }
}
