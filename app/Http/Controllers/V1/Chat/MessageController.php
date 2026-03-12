<?php

namespace App\Http\Controllers\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function index(Conversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()->with('sender')->oldest()->get();
        return response()->json($messages);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'text' => $validated['text'],
        ]);

        return response()->json($message, 201);
    }
}