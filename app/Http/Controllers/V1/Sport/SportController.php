<?php

namespace App\Http\Controllers\V1\Sport;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportResource;
use App\Models\Sport;
use Symfony\Component\HttpFoundation\Response;

class SportController extends Controller
{
    public function index()
    {
        $allowedSports = [
            'natación',
            'fútbol',
            'voleibol',
            'baloncesto',
            'tenis',
            'bádminton',
            'béisbol',
            'balonmano',
            'hockey',
            'rugby',
            'Fórmula 1',
        ];

        $sports = Sport::query()
            ->whereIn('name', $allowedSports)
            ->get()
            ->unique(fn (Sport $sport) => mb_strtolower($sport->name))
            ->values();

        return response()->json([
            'message' => 'Sports List',
            'data' => SportResource::collection($sports),
        ], Response::HTTP_OK);
    }
}
