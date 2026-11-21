<?php

namespace App\Http\Controllers\V1\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\SavedEvent;
use App\Support\RolePermissionPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $viewerId = $request->user()?->id;

        $events = Event::query()
            ->with(['user.profile', 'user.avatar', 'sport'])
            ->withExists([
                'participants as is_participating' => fn ($q) => $q->where('user_id', $viewerId),
                'savedBy as is_saved' => fn ($q) => $q->where('user_id', $viewerId),
            ])
            ->visibleTo($request->user())
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

        $events->getCollection()->transform(
            fn (Event $event) => $this->presentEvent($event, $viewerId, includeParticipants: false, useLoadedFlags: true)
        );

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        RolePermissionPolicy::denyUnless(
            RolePermissionPolicy::canCreateEvents($request->user()),
            'Tu rol no puede crear eventos.'
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:50'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'location' => ['required', 'string', 'max:255'],
            'route_text' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'sport_id' => ['required', 'exists:sports,id'],
            'description' => ['required', 'string'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'organizer_name' => ['required', 'string', 'max:255'],
            'organizer_contact' => ['required', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $event = Event::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $this->presentEvent(
                $event->load(['user.profile', 'user.avatar', 'sport']),
                Auth::id()
            ),
        ], SymfonyResponse::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        $event = Event::with([
            'user.profile',
            'user.avatar',
            'sport',
            'participants.user.profile',
            'participants.user.avatar',
        ])
            ->visibleTo(Auth::user())
            ->findOrFail($id);

        return response()->json([
            'data' => $this->presentEvent($event, Auth::id(), includeParticipants: true),
        ]);
    }

    public function participate(string $id): JsonResponse
    {
        $event = Event::query()->visibleTo(Auth::user())->findOrFail($id);

        $existing = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Already registered',
                'data' => $existing,
            ]);
        }

        if ($event->capacity !== null && $event->participants_count >= $event->capacity) {
            return response()->json([
                'message' => 'El evento está lleno.',
                'error' => 'capacity_full',
            ], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $participant = EventParticipant::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'status' => 'registered',
        ]);
        $event->increment('participants_count');

        return response()->json([
            'message' => 'Registered for event successfully',
            'data' => $participant,
        ], SymfonyResponse::HTTP_CREATED);
    }

    public function leave(string $id): JsonResponse
    {
        $event = Event::query()->visibleTo(Auth::user())->findOrFail($id);
        $deleted = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->delete();

        if ($deleted > 0 && $event->participants_count > 0) {
            $event->decrement('participants_count');
        }

        return response()->json(['message' => 'Left event successfully']);
    }

    public function linkActivity(Request $request, string $id): JsonResponse
    {
        $event = Event::query()->visibleTo(Auth::user())->findOrFail($id);
        $participant = EventParticipant::query()
            ->where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'activity_url' => ['required', 'url', 'max:2048'],
            'activity_note' => ['nullable', 'string', 'max:500'],
        ]);

        $participant->update([
            'activity_url' => $validated['activity_url'],
            'activity_note' => $validated['activity_note'] ?? null,
            'linked_at' => now(),
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => 'Activity linked',
            'data' => $participant->fresh(),
        ]);
    }

    public function calendar(string $id): Response
    {
        $event = Event::query()->visibleTo(Auth::user())->findOrFail($id);
        $date = optional($event->event_date)->format('Ymd') ?: now()->format('Ymd');
        $time = $event->event_time
            ? str_replace(':', '', substr((string) $event->event_time, 0, 5)).'00'
            : '090000';
        $dtStart = $date.'T'.$time;
        $summary = $this->icsEscape($event->name);
        $location = $this->icsEscape(trim(implode(', ', array_filter([
            $event->location,
            $event->city,
            $event->country,
        ]))));
        $description = $this->icsEscape((string) $event->description);
        $uid = $event->id.'@hubsport';

        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//HubSport//Events//ES\r\nBEGIN:VEVENT\r\nUID:{$uid}\r\nDTSTAMP:".now()->format('Ymd\THis\Z')."\r\nDTSTART:{$dtStart}\r\nSUMMARY:{$summary}\r\nLOCATION:{$location}\r\nDESCRIPTION:{$description}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="hubsport-event-'.$event->id.'.ics"',
        ]);
    }

    public function save(string $id): JsonResponse
    {
        $event = Event::query()->visibleTo(Auth::user())->findOrFail($id);

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

    /**
     * @return array<string, mixed>
     */
    private function presentEvent(
        Event $event,
        ?string $viewerId,
        bool $includeParticipants = false,
        bool $useLoadedFlags = false
    ): array {
        $payload = $event->toArray();
        $capacity = $event->capacity;
        $count = (int) $event->participants_count;
        $payload['spots_left'] = $capacity === null ? null : max(0, $capacity - $count);
        $payload['is_full'] = $capacity !== null && $count >= $capacity;

        if ($useLoadedFlags) {
            $payload['is_participating'] = (bool) ($event->is_participating ?? false);
            $payload['is_saved'] = (bool) ($event->is_saved ?? false);
        } else {
            $payload['is_participating'] = $viewerId
                ? $event->participants()->where('user_id', $viewerId)->exists()
                : false;
            $payload['is_saved'] = $viewerId
                ? SavedEvent::query()->where('event_id', $event->id)->where('user_id', $viewerId)->exists()
                : false;
        }

        if ($includeParticipants) {
            $payload['participants'] = $event->participants->map(function (EventParticipant $p) {
                $user = $p->user;

                return [
                    'id' => $p->id,
                    'status' => $p->status,
                    'activity_url' => $p->activity_url,
                    'activity_note' => $p->activity_note,
                    'linked_at' => $p->linked_at,
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'slug' => $user->slug,
                        'avatar_url' => method_exists($user, 'getFirstMediaUrl')
                            ? ($user->getFirstMediaUrl('avatar') ?: null)
                            : null,
                        'first_name' => $user->profile?->first_name,
                        'last_name' => $user->profile?->last_name,
                    ] : null,
                ];
            })->values()->all();
        }

        return $payload;
    }

    private function icsEscape(string $value): string
    {
        return str_replace(
            ["\\", ";", ",", "\n", "\r"],
            ["\\\\", "\\;", "\\,", '\\n', ''],
            $value
        );
    }
}
