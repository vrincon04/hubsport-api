<?php

namespace App\Http\Controllers\V1\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\RolePermissionPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function index(Post $post): JsonResponse
    {
        $comments = $post->comments()->with('user.avatar')->latest()->paginate(20);
        return response()->json($comments);
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        RolePermissionPolicy::denyUnless(
            RolePermissionPolicy::canWriteContent($request->user()),
            'Tu cuenta aún no puede comentar.'
        );

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return response()->json($comment->load('user.avatar'), 201);
    }
}