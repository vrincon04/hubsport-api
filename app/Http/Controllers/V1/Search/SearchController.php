<?php

namespace App\Http\Controllers\V1\Search;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\News;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across Users, Posts and News.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->query('q');

        if (! $query) {
            return response()->json([
                'message' => 'Search',
                'data' => [
                    'users' => [],
                    'posts' => [],
                    'news' => [],
                ],
            ]);
        }

        $users = User::query()
            ->where('name', 'like', "%{$query}%")
            ->with(['profile', 'avatar'])
            ->limit(5)
            ->get();

        $posts = Post::query()
            ->where('body', 'like', "%{$query}%")
            ->with(['user.profile', 'user.avatar'])
            ->limit(10)
            ->get();

        $news = News::query()
            ->where('title', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return response()->json([
            'message' => 'Search results',
            'data' => [
                'users' => UserResource::collection($users),
                'posts' => PostResource::collection($posts),
                'news' => $news,
            ],
        ]);
    }
}
