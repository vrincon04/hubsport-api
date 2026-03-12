<?php

namespace App\Http\Controllers\V1\News;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = News::with('sport')->latest();

        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        return response()->json($query->paginate(15));
    }

    public function show(News $news): JsonResponse
    {
        return response()->json($news->load('sport'));
    }
}