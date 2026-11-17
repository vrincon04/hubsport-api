<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Conversation */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = $request->user()?->id;
        $other = $this->relationLoaded('users')
            ? $this->users->first(fn ($user) => $user->id !== $viewerId)
            : null;
        $last = $this->relationLoaded('messages') ? $this->messages->first() : null;
        $text = $last?->text;

        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'users' => UserSummaryResource::collection($this->whenLoaded('users')),
            'other_user' => $other ? new UserSummaryResource($other) : null,
            'last_message' => $last ? [
                'id' => $last->id,
                'content' => $text,
                'text' => $text,
                'body' => $text,
                'created_at' => $last->created_at,
            ] : null,
        ];
    }
}
