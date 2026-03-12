<?php

namespace App\Http\Controllers\V1\Sport;

use App\Http\Controllers\Controller;
use App\Models\MatchResult;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MatchResultController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MatchResult::with('sport')->latest('match_date');

        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        return response()->json($query->get());
    }
}