<?php

namespace App\Actions\Post;

use App\Http\Requests\Post\StorePostRequest;
use App\Models\Post;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePostAction
{
    use AsAction;

    public function handle(StorePostRequest $request): Post
    {
        $user = $request->user();
        $post = $user->posts()->create($request->validated());

        if ($request->hasFile('gallery')) {
            $post->addMultipleMediaFromRequest(['gallery'])
                ->each(function ($media) {
                    $media->toMediaCollection('gallery');
                });
        }

        return $post;
    }
}
