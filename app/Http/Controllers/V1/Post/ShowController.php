<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

class ShowController extends Controller
{
    public function __invoke(Post $post)
    {
        return new PostResource($post->load(['user.profile', 'user.avatar', 'likes.user.avatar', 'likes.user.profile', 'gallery']));
    }
}
