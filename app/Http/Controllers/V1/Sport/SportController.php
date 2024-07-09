<?php

namespace App\Http\Controllers\V1\Sport;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportResource;
use App\Models\Sport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SportController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Sports List',
            'data' => SportResource::collection(Sport::all()),
        ], Response::HTTP_OK);
    }
}
