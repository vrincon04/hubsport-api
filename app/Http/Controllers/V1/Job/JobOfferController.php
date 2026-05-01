<?php

namespace App\Http\Controllers\V1\Job;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\JobOffer;
use App\Models\JobApplication;
use App\Models\SavedJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class JobOfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobOffer::with(['user', 'sport'])->latest();
        
        if ($request->filled('country')) {
            $query->where('location', 'like', '%' . $request->country . '%');
        }
        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }
        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        $perPage = (int) $request->query('per_page', 15);

        return response()->json(
            $query->paginate($perPage)->through(fn (JobOffer $job) => $this->serializeJob($job)),
            Response::HTTP_OK
        );
    }


    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contract_type' => 'required|string',
            'description' => 'required|string',
            'application_type' => 'required|in:simple,web',
            'application_url' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'sport_id' => 'nullable|exists:sports,id',
        ]);

        $job = JobOffer::create([
            ...$request->only([
                'title',
                'sport_id',
                'company',
                'location',
                'contract_type',
                'application_type',
                'application_url',
                'description',
                'deadline',
            ]),
            'user_id' => Auth::id(),
        ])->load(['user', 'sport']);

        return response()->json(['message' => 'Job created successfully', 'data' => $this->serializeJob($job)], Response::HTTP_CREATED);
    }


    public function show($id): JsonResponse
    {
        $job = JobOffer::with(['user', 'sport'])->findOrFail($id);
        return response()->json(['data' => $this->serializeJob($job)], Response::HTTP_OK);
    }


    public function apply(Request $request, $id): JsonResponse
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        $job = JobOffer::findOrFail($id);

        abort_if($job->user_id === Auth::id(), Response::HTTP_FORBIDDEN, 'No puedes aplicar a tu propio empleo.');

        $application = JobApplication::updateOrCreate(
            [
                'job_offer_id' => $job->id,
                'user_id' => Auth::id(),
            ],
            [
                'message' => $request->input('message'),
                'status' => 'pending',
            ]
        );

        return response()->json(['message' => 'Applied successfully', 'data' => $application], Response::HTTP_CREATED);
    }


    public function cancelApplication(Request $request, $id): JsonResponse
    {
        JobApplication::where('job_offer_id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['message' => 'Application cancelled successfully'], Response::HTTP_OK);
    }


    public function save(Request $request, $id): JsonResponse
    {
        $job = JobOffer::findOrFail($id);

        SavedJob::firstOrCreate([
            'job_offer_id' => $job->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Job saved successfully'], Response::HTTP_OK);
    }


    public function unsave(Request $request, $id): JsonResponse
    {
        SavedJob::where('job_offer_id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Job unsaved successfully'], Response::HTTP_OK);
    }


    public function myApplications(Request $request): JsonResponse
    {
        $applications = JobApplication::with(['jobOffer.user', 'jobOffer.sport'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn (JobApplication $application) => [
                ...$this->serializeJob($application->jobOffer),
                'application' => [
                    'id' => $application->id,
                    'status' => $application->status,
                    'message' => $application->message,
                    'created_at' => $application->created_at,
                ],
            ]);

        return response()->json(['data' => $applications], Response::HTTP_OK);
    }


    public function myOffers(Request $request): JsonResponse
    {
        $offers = JobOffer::with(['user', 'sport'])
            ->withCount('applications')
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn (JobOffer $job) => $this->serializeJob($job));

        return response()->json(['data' => $offers], Response::HTTP_OK);
    }


    public function mySaved(Request $request): JsonResponse
    {
        $saved = SavedJob::with(['jobOffer.user', 'jobOffer.sport'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn (SavedJob $savedJob) => $this->serializeJob($savedJob->jobOffer));

        return response()->json(['data' => $saved], Response::HTTP_OK);
    }


    public function applicants(Request $request, $id): JsonResponse
    {
        $job = JobOffer::where('user_id', Auth::id())->findOrFail($id);

        $applications = $job->applications()
            ->with(['applicant.profile.country', 'applicant.profile.sport', 'applicant.avatar'])
            ->latest()
            ->get()
            ->map(fn (JobApplication $application) => [
                'id' => $application->id,
                'status' => $application->status,
                'message' => $application->message,
                'created_at' => $application->created_at,
                'user' => new UserResource($application->applicant),
            ]);

        return response()->json(['data' => $applications], Response::HTTP_OK);
    }


    private function serializeJob(?JobOffer $job): array
    {
        if (! $job) {
            return [];
        }

        $userId = Auth::id();

        $hasApplied = $userId
            ? JobApplication::where('job_offer_id', $job->id)->where('user_id', $userId)->exists()
            : false;

        $isSaved = $userId
            ? SavedJob::where('job_offer_id', $job->id)->where('user_id', $userId)->exists()
            : false;

        return [
            'id' => $job->id,
            'title' => $job->title,
            'description' => $job->description,
            'company' => $job->company,
            'location' => $job->location,
            'contract_type' => $job->contract_type,
            'modality' => $job->contract_type,
            'application_type' => $job->application_type,
            'application_url' => $job->application_url,
            'deadline' => $job->deadline,
            'sport_id' => $job->sport_id,
            'sport_name' => $job->sport?->name,
            'experience_level' => $job->sport?->name ?? 'Deporte',
            'user' => $job->relationLoaded('user') && $job->user ? new UserResource($job->user) : null,
            'is_owner' => $userId === $job->user_id,
            'is_saved' => $isSaved,
            'has_applied' => $hasApplied,
            'applications_count' => $job->applications_count ?? $job->applications()->count(),
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
        ];
    }

}
