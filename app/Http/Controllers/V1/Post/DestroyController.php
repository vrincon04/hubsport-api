<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;

class DestroyController extends Controller
{
    public function __invoke(Post $post)
    {
        $post->load('user');

        if ($post->user_id !== auth()->id()) {
            abort(403);
        }

        $post->delete();

        return response()->noContent();
    }
}
