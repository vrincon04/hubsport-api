<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class StoreController extends Controller
{
    public function __invoke(StorePostRequest $request)
    {
        return new PostResource(Post::create($request->validated()));
    }
}
