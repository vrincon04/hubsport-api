<?php

namespace App\Http\Controllers\V1\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\SavedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $events = Event::query()
            ->with(['user.profile', 'user.avatar', 'sport'])
            ->when($request->filled('sport_id'), fn ($query) => $query->where('sport_id', $request->query('sport_id')))
            ->when($request->filled('country'), fn ($query) => $query->where('country', 'like', '%'.$request->query('country').'%'))
            ->when($request->filled('city'), fn ($query) => $query->where('city', 'like', '%'.$request->query('city').'%'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->query('q');
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('organizer_name', 'like', "%{$term}%")
                        ->orWhereHas('sport', fn ($sport) => $sport->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('event_date')
            ->paginate(15);

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'location' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'sport_id' => ['required', 'exists:sports,id'],
            'description' => ['required', 'string'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'organizer_name' => ['required', 'string', 'max:255'],
            'organizer_contact' => ['required', 'string', 'max:255'],
        ]);

        $event = Event::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event->load(['user.profile', 'user.avatar', 'sport']),
        ], Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        $event = Event::with(['user.profile', 'user.avatar', 'sport'])->findOrFail($id);

        return response()->json($event);
    }

    public function participate(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $participant = EventParticipant::firstOrCreate([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
        ]);

        if ($participant->wasRecentlyCreated) {
            $event->increment('participants_count');
        }

        return response()->json([
            'message' => 'Registered for event successfully',
            'data' => $participant,
        ], Response::HTTP_CREATED);
    }

    public function save(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        SavedEvent::firstOrCreate([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Event saved successfully']);
    }

    public function unsave(string $id): JsonResponse
    {
        SavedEvent::where('event_id', $id)->where('user_id', Auth::id())->delete();

        return response()->json(['message' => 'Event unsaved successfully']);
    }
}
