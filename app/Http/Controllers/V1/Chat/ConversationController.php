<?php

namespace App\Http\Controllers\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conversations = $request->user()->conversations()
            ->with([
                'users.avatar',
                'users.profile.country',
                'users.profile.sport',
                'messages' => function ($query) {
                    $query->latest()->limit(1);
                },
            ])
            ->latest()
            ->get();

        return ConversationResource::collection($conversations)->response();
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

        if (! $conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$request->user()->id, $validated['user_id']]);
        }

        $conversation->load([
            'users.avatar',
            'users.profile.country',
            'users.profile.sport',
            'messages' => fn ($query) => $query->latest()->limit(1),
        ]);

        return (new ConversationResource($conversation))
            ->response()
            ->setStatusCode(201);
    }
}
