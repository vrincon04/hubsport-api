<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

class IndexController extends Controller
{
    public function __invoke()
    {
        $viewer = request()->user();

        $post = Post::with(['user.profile', 'user.avatar', 'likes.user.avatar', 'likes.user.profile', 'gallery'])
            ->withCount(['likes'])
            ->visibleTo($viewer)
            ->when(request('user_id'), function ($query) {
                return $query->where('user_id', request('user_id'));
            })
            ->when(request()->boolean('my_sport') || request()->filled('sport_id'), function ($query) use ($viewer) {
                $sportId = request('sport_id') ?: $viewer?->profile?->sport_id;
                if (! $sportId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas(
                    'user.profile',
                    fn ($profile) => $profile->where('sport_id', $sportId)
                );
            })
            ->orderByDesc('created_at');

        return PostResource::collection($post->paginate());
    }

}
