<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'post_type' => $this->post_type,
            'location' => $this->location,
            'activity_url' => $this->activity_url,
            'status' => $this->status,
            'published_at' => $this->published_at,

            'user' => new UserResource($this->whenLoaded('user')),

            'gallery' => MediaResource::collection($this->whenLoaded('gallery')),

            'likes' => $this->whenLoaded('likes', function () {
                return $this->likes
                    ->map(fn ($like) => $like->user)
                    ->filter()
                    ->map(fn ($user) => new UserSummaryResource($user))
                    ->values();
            }),
            'likes_count' => $this->whenCounted('likes'),
        ];
    }
}
