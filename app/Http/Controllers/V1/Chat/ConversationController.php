<?php

namespace App\Http\Controllers\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conversations = $request->user()->conversations()
            ->with(['users', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])
            ->latest()
            ->get();

        return response()->json($conversations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if conversation already exists between these two
        $conversation = $request->user()->conversations()
            ->whereHas('users', function ($query) use ($validated) {
            $query->where('users.id', $validated['user_id']);
        })
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$request->user()->id, $validated['user_id']]);
        }

        return response()->json($conversation->load('users'), 201);
    }
}