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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'phone_verified_at' => $this->phone_verified_at,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at,
            'verified_badge' => $this->verified_badge,
            'profile_type' => $this->profile_type,
            'city' => $this->city,
            'bio' => $this->bio,
            'birth_date' => $this->birth_date,
            'position' => $this->position,
            'sport_level' => $this->sport_level,
            'current_team' => $this->current_team,
            'social_links' => $this->social_links,
            'full_name' => $this->full_name,

            'experience' => $this->experience,
            'achievements' => $this->achievements,
            'education' => $this->education,

            'user' => new UserResource($this->whenLoaded('user')),
            'country' => new CountryResource($this->whenLoaded('country')),
            'sport' => new SportResource($this->whenLoaded('sport')),
        ];
    }
}
