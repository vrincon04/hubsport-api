<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

class IndexController extends Controller
{
    public function __invoke()
    {
        $post = Post::with(['user.profile', 'user.avatar', 'likes.user.avatar', 'likes.user.profile', 'gallery'])
            ->withCount(['likes'])->orderByDesc('created_at');

        return PostResource::collection($post->paginate());
    }
}
