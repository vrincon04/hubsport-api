<?php

namespace App\Http\Controllers\V1\Post;

use App\Actions\Post\CreatePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Support\RolePermissionPolicy;

class StoreController extends Controller
{
    public function __invoke(StorePostRequest $request)
    {
        RolePermissionPolicy::denyUnless(
            RolePermissionPolicy::canWriteContent($request->user()),
            'Tu cuenta aún no puede publicar.'
        );

        $post = CreatePostAction::run($request);

        return new PostResource($post->load('user.profile', 'user.avatar', 'gallery'));
    }
}
