<?php

namespace App\Http\Controllers\V1\Sponsorship;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SponsorshipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sponsorships = Sponsorship::query()
            ->with(['user.profile', 'user.avatar', 'sport'])
            ->when($request->filled('sport_id'), fn ($query) => $query->where('sport_id', $request->query('sport_id')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->query('q');
                $query->where(function ($q) use ($term) {
                    $q->where('brand_name', 'like', "%{$term}%")
                        ->orWhere('sponsorship_type', 'like', "%{$term}%")
                        ->orWhere('requirements', 'like', "%{$term}%")
                        ->orWhere('benefits', 'like', "%{$term}%")
                        ->orWhereHas('sport', fn ($sport) => $sport->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(15);

        return response()->json($sponsorships);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_name' => ['required', 'string', 'max:255'],
            'sport_id' => ['required', 'exists:sports,id'],
            'sponsorship_type' => ['required', 'string', 'max:255'],
            'requirements' => ['required', 'string'],
            'benefits' => ['required', 'string'],
            'contact' => ['nullable', 'string', 'max:255'],
        ]);

        $sponsorship = Sponsorship::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        return response()->json([
            'message' => 'Sponsorship created successfully',
            'data' => $sponsorship->load(['user.profile', 'user.avatar', 'sport']),
        ], Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(
            Sponsorship::with(['user.profile', 'user.avatar', 'sport'])->findOrFail($id)
        );
    }
}
