<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'notifications_count' => $this->notifications_count,
            'read_notifications_count' => $this->read_notifications_count,
            'unread_notifications_count' => $this->unread_notifications_count,

            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'avatar' => new MediaResource($this->whenLoaded('avatar')),
        ];
    }
}
