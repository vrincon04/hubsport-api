<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

class IndexController extends Controller
{
    public function __invoke()
    {
        return PostResource::collection(Post::paginate());
    }
}
