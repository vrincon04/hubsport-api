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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'status' => $this->status,
            'published_at' => $this->published_at,

            'user' => new UserResource($this->whenLoaded('user')),

            'gallery' => MediaResource::collection($this->whenLoaded('gallery')),

            'likes' => $this->whenLoaded('likes'),
            'likes_count' => $this->whenLoaded('likes'),
        ];
    }
}
