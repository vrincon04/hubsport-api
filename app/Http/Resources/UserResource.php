<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isDemo = (bool) config('demo.enabled')
            && hash_equals((string) config('demo.user_email'), (string) $this->email);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'is_email_verified' => (bool) $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'notifications_count' => $this->notifications_count,
            'read_notifications_count' => $this->read_notifications_count,
            'unread_notifications_count' => $this->unread_notifications_count,

            'followers_count' => $this->followers_count ?? $this->followers()->count(),
            'following_count' => $this->following_count ?? $this->following()->count(),
            'posts_count' => $this->posts_count ?? $this->posts()->count(),

            'connection_status' => $request->user() ? $request->user()->getConnectionStatusWith($this->resource) : 'none',
            'stats' => $this->getSportsStats(),

            'profile' => new ProfileResource($this->whenLoaded('profile')),

            'avatar' => new MediaResource($this->whenLoaded('avatar')),

            'demo_preview' => $isDemo,
            'feature_flags' => $isDemo ? [
                'billing' => true,
                'groups' => true,
                'collaborations' => true,
                'store' => true,
                'events' => true,
                'sponsorships' => true,
            ] : [],
        ];
    }
}
