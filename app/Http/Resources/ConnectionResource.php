<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Connection */
class ConnectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sender' => $this->when(
                $this->relationLoaded('user') && $this->user,
                fn () => new UserResource($this->user)
            ),
            'recipient' => $this->when(
                $this->relationLoaded('connectedUser') && $this->connectedUser,
                fn () => new UserResource($this->connectedUser)
            ),
        ];
    }
}
