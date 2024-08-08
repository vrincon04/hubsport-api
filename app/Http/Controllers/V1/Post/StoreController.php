<?php

namespace App\Http\Controllers\V1\Post;

use App\Actions\Post\CreatePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class StoreController extends Controller
{
    public function __invoke(StorePostRequest $request)
    {
        $post = CreatePostAction::run($request);
        return new PostResource($post->load('user.profile', 'user.avatar', 'gallery'));
    }
}
