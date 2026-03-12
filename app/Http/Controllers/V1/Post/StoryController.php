<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    /**
     * Display a listing of stories from followed users.
     */
    public function index()
    {
        $followingIds = Auth::user()->following()->pluck('connected_user_id');

        // Include own stories and followed users' stories
        $stories = Story::active()
            ->whereIn('user_id', $followingIds->push(Auth::id()))
            ->with('user:id,name')
            ->latest()
            ->get()
            ->groupBy('user_id');

        return response()->json([
            'data' => $stories,
        ]);
    }

    /**
     * Store a newly created story in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'media_url' => 'required|url',
            'type' => 'required|in:image,video',
        ]);

        $story = Auth::user()->stories()->create([
            'media_url' => $request->media_url,
            'type' => $request->type,
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'message' => 'Story uploaded successfully',
            'data' => $story,
        ], 210);
    }
}