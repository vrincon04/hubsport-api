<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;

class LikeController extends Controller
{
    public function __invoke(Post $post)
    {
        $user = auth()->user();

        if (!$user->hasLiked($post))
            auth()->user()->like($post);

        return response()->noContent();
    }
}
