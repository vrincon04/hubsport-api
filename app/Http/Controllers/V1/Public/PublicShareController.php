<?php

namespace App\Http\Controllers\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\News;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PublicShareController extends Controller
{
    public function post(Post $post): JsonResponse
    {
        abort_if($post->trashed(), 404);
        if ($post->status !== null && $post->status !== '' && $post->status !== 'published') {
            abort(404);
        }

        $post->load(['user.profile.sport', 'user.avatar', 'gallery'])->loadCount('likes');

        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'post',
                'post' => new PostResource($post),
                'author' => [
                    'name' => $post->user?->name,
                    'handle' => $post->user?->slug ? '@'.$post->user->slug : null,
                    'sport' => $post->user?->profile?->sport?->name,
                ],
                'cta' => [
                    'message' => 'Crea una cuenta HubSport para comentar, conectar y ver más.',
                    'register_path' => '/register',
                ],
            ],
            'error' => null,
        ]);
    }

    public function news(News $news): JsonResponse
    {
        $news->load('sport');

        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'news',
                'news' => $news,
                'cta' => [
                    'message' => 'Únete a HubSport para seguir noticias y atletas de tu deporte.',
                    'register_path' => '/register',
                ],
            ],
            'error' => null,
        ]);
    }
}
