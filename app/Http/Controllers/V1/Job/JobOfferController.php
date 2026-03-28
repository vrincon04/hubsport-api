<?php

namespace App\Http\Controllers\V1\Job;

use App\Http\Controllers\Controller;
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

        return response()->json($query->paginate(15), Response::HTTP_OK);
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
        ]);

        $job = JobOffer::create(array_merge(
            $request->all(),
            ['user_id' => Auth::id()]
        ));

        return response()->json(['message' => 'Job created successfully', 'data' => $job], Response::HTTP_CREATED);
    }


    public function show($id): JsonResponse
    {
        $job = JobOffer::with(['user', 'sport'])->findOrFail($id);
        return response()->json($job, Response::HTTP_OK);
    }


    public function apply(Request $request, $id): JsonResponse
    {
        $job = JobOffer::findOrFail($id);

        $application = JobApplication::firstOrCreate([
            'job_offer_id' => $job->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Applied successfully', 'data' => $application], Response::HTTP_CREATED);
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
        $applications = JobApplication::with('jobOffer.company')->where('user_id', Auth::id())->latest()->get();
        return response()->json($applications, Response::HTTP_OK);
    }


    public function myOffers(Request $request): JsonResponse
    {
        $offers = JobOffer::where('user_id', Auth::id())->latest()->get();
        return response()->json($offers, Response::HTTP_OK);
    }


    public function mySaved(Request $request): JsonResponse
    {
        $saved = SavedJob::with('jobOffer')->where('user_id', Auth::id())->latest()->get();
        return response()->json($saved, Response::HTTP_OK);
    }

}
