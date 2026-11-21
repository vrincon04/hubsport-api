<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\AgePolicy;
use App\Support\RolePermissionPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Profile */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $subjectId = (string) $this->user_id;
        $isSelf = $viewer && (string) $viewer->id === $subjectId;
        $isMinor = AgePolicy::isMinor($this->birth_date);
        $canSeeSensitive = ! $isMinor || $isSelf;

        if ($isMinor && $viewer && ! $isSelf) {
            $subject = $this->relationLoaded('user')
                ? $this->user
                : User::query()->find($subjectId);
            $canSeeSensitive = $subject
                ? RolePermissionPolicy::canViewMinorSensitiveFields($viewer, $subject)
                : false;
        }

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $canSeeSensitive ? $this->phone_number : null,
            'phone_verified_at' => $isSelf ? $this->phone_verified_at : null,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at,
            'verified_badge' => $this->verified_badge,
            'profile_type' => $this->profile_type,
            'account_status' => $this->account_status,
            'is_minor' => $isMinor,
            'guardian_email' => $isSelf ? $this->guardian_email : null,
            'guardian_confirmed_at' => $isSelf ? $this->guardian_confirmed_at : null,
            'city' => $this->city,
            'bio' => $this->bio,
            'birth_date' => $isSelf ? $this->birth_date : null,
            'height_cm' => $this->height_cm,
            'weight_kg' => $this->weight_kg,
            'wingspan_cm' => $this->wingspan_cm,
            'laterality' => $this->laterality,
            'physical_extras' => $this->physical_extras,
            'sport_id' => $this->sport_id,
            'sport_locked_at' => $this->sport_locked_at,
            'position' => $this->position,
            'sport_level' => $this->sport_level,
            'current_team' => $canSeeSensitive ? $this->current_team : null,
            'organization_name' => $isSelf ? $this->organization_name : null,
            'rnc' => $isSelf ? $this->rnc : null,
            'organization_document_url' => $isSelf ? $this->organization_document_url : null,
            'organization_website' => $this->organization_website,
            'social_links' => $this->social_links,
            'full_name' => $this->full_name,

            'experience' => $this->experience,
            'achievements' => $this->achievements,
            'education' => $this->education,
            'stats' => $this->stats,

            'user' => new UserResource($this->whenLoaded('user')),
            'country' => new CountryResource($this->whenLoaded('country')),
            'sport' => new SportResource($this->whenLoaded('sport')),
        ];
    }
}
