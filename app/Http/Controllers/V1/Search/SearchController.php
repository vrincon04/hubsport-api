<?php

namespace App\Http\Controllers\V1\Search;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across Users, Posts and News.
     */
    public function __invoke(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json(['data' => []]);
        }

        $users = User::where('name', 'like', "%{$query}%")->limit(5)->get();
        $posts = Post::where('text', 'like', "%{$query}%")->with('user:id,name')->limit(10)->get();
        $news = News::where('title', 'like', "%{$query}%")->limit(5)->get();

        return response()->json([
            'data' => [
                'users' => $users,
                'posts' => $posts,
                'news' => $news,
            ],
        ]);
    }
}