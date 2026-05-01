<?php

namespace App\Http\Controllers\V1\Search;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Event;
use App\Models\JobOffer;
use App\Models\News;
use App\Models\Post;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across users, posts, news and opportunities.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $hasFilters = collect([
            'profile_type',
            'sport_id',
            'country_id',
            'city',
            'sport_level',
            'opportunity_type',
        ])->contains(fn (string $key) => $request->filled($key));

        if ($query === '' && ! $hasFilters) {
            return response()->json([
                'message' => 'Search',
                'data' => [
                    'users' => [],
                    'posts' => [],
                    'news' => [],
                    'opportunities' => [],
                    'sponsorships' => [],
                    'events' => [],
                ],
            ]);
        }

        $users = User::query()
            ->with(['profile.country', 'profile.sport', 'avatar'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhereHas('profile', function ($profile) use ($query) {
                            $profile->where('first_name', 'like', "%{$query}%")
                                ->orWhere('last_name', 'like', "%{$query}%")
                                ->orWhere('city', 'like', "%{$query}%")
                                ->orWhere('current_team', 'like', "%{$query}%")
                                ->orWhere('sport_level', 'like', "%{$query}%")
                                ->orWhereHas('sport', fn ($sport) => $sport->where('name', 'like', "%{$query}%"))
                                ->orWhereHas('country', fn ($country) => $country->where('name', 'like', "%{$query}%"));
                        });
                });
            })
            ->when($request->filled('profile_type'), fn ($builder) => $builder->whereHas('profile', fn ($profile) => $profile->where('profile_type', $request->query('profile_type'))))
            ->when($request->filled('sport_id'), fn ($builder) => $builder->whereHas('profile', fn ($profile) => $profile->where('sport_id', $request->query('sport_id'))))
            ->when($request->filled('country_id'), fn ($builder) => $builder->whereHas('profile', fn ($profile) => $profile->where('country_id', $request->query('country_id'))))
            ->when($request->filled('city'), fn ($builder) => $builder->whereHas('profile', fn ($profile) => $profile->where('city', 'like', '%'.$request->query('city').'%')))
            ->when($request->filled('sport_level'), fn ($builder) => $builder->whereHas('profile', fn ($profile) => $profile->where('sport_level', $request->query('sport_level'))))
            ->limit(15)
            ->get();

        $posts = Post::query()
            ->when($query !== '', fn ($builder) => $builder->where('body', 'like', "%{$query}%"))
            ->with(['user.profile', 'user.avatar'])
            ->limit(10)
            ->get();

        $news = News::query()
            ->when($query !== '', fn ($builder) => $builder->where('title', 'like', "%{$query}%"))
            ->limit(5)
            ->get();

        $jobs = JobOffer::query()
            ->with(['user.profile', 'user.avatar', 'sport'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('company', 'like', "%{$query}%")
                        ->orWhere('location', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhereHas('sport', fn ($sport) => $sport->where('name', 'like', "%{$query}%"));
                });
            })
            ->when($request->filled('sport_id'), fn ($builder) => $builder->where('sport_id', $request->query('sport_id')))
            ->when($request->filled('country_id'), fn ($builder) => $builder->where('location', 'like', '%'.$request->query('country_id').'%'))
            ->when($request->filled('city'), fn ($builder) => $builder->where('location', 'like', '%'.$request->query('city').'%'))
            ->when($request->filled('opportunity_type'), function ($builder) use ($request) {
                if ($request->query('opportunity_type') === 'job') {
                    return $builder;
                }

                return $builder->whereRaw('1 = 0');
            })
            ->latest()
            ->limit(10)
            ->get();

        $sponsorships = Sponsorship::query()
            ->with(['user.profile', 'user.avatar', 'sport'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($q) use ($query) {
                    $q->where('brand_name', 'like', "%{$query}%")
                        ->orWhere('sponsorship_type', 'like', "%{$query}%")
                        ->orWhere('requirements', 'like', "%{$query}%")
                        ->orWhere('benefits', 'like', "%{$query}%")
                        ->orWhereHas('sport', fn ($sport) => $sport->where('name', 'like', "%{$query}%"));
                });
            })
            ->when($request->filled('sport_id'), fn ($builder) => $builder->where('sport_id', $request->query('sport_id')))
            ->when($request->filled('opportunity_type'), function ($builder) use ($request) {
                if ($request->query('opportunity_type') === 'sponsorship') {
                    return $builder;
                }

                return $builder->whereRaw('1 = 0');
            })
            ->latest()
            ->limit(10)
            ->get();

        $events = Event::query()
            ->with(['user.profile', 'user.avatar', 'sport'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('location', 'like', "%{$query}%")
                        ->orWhere('city', 'like', "%{$query}%")
                        ->orWhere('country', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('organizer_name', 'like', "%{$query}%")
                        ->orWhereHas('sport', fn ($sport) => $sport->where('name', 'like', "%{$query}%"));
                });
            })
            ->when($request->filled('sport_id'), fn ($builder) => $builder->where('sport_id', $request->query('sport_id')))
            ->when($request->filled('city'), fn ($builder) => $builder->where('city', 'like', '%'.$request->query('city').'%'))
            ->when($request->filled('opportunity_type'), function ($builder) use ($request) {
                if ($request->query('opportunity_type') === 'event') {
                    return $builder;
                }

                return $builder->whereRaw('1 = 0');
            })
            ->orderBy('event_date')
            ->limit(10)
            ->get();

        return response()->json([
            'message' => 'Search results',
            'data' => [
                'users' => UserResource::collection($users),
                'posts' => PostResource::collection($posts),
                'news' => $news,
                'opportunities' => $jobs,
                'sponsorships' => $sponsorships,
                'events' => $events,
            ],
        ]);
    }
}
