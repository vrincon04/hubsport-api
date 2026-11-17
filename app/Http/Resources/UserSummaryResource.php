<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $avatar = $this->relationLoaded('avatar') ? $this->avatar : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $avatar ? new MediaResource($avatar) : null,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
        ];
    }
}
