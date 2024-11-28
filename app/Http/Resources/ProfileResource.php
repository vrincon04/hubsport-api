<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Profile */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'bio' => $this->bio,
            'birth_date' => $this->birth_date,
            'full_name' => $this->full_name,

            'user' => new UserResource($this->whenLoaded('user')),
            'country' => new CountryResource($this->whenLoaded('country')),
            'sport' => new SportResource($this->whenLoaded('sport')),
        ];
    }
}
