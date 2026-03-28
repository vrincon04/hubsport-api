<?php

namespace App\Http\Controllers\V1\Job;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\JobApplication;
use App\Models\SavedJob;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JobOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = JobOffer::with(['user', 'sport'])->latest();
        
        if ($request->has('country')) {
            $query->where('location', 'like', '%' . $request->country . '%');
        }
        if ($request->has('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }
        if ($request->has('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        return response()->json($query->paginate(15), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'company' => 'required|string',
            'location' => 'required|string',
            'contract_type' => 'required|string',
            'description' => 'required|string',
            'application_type' => 'required|in:simple,web',
        ]);

        $job = JobOffer::create(array_merge(
            $request->all(),
            ['user_id' => $request->user()->id]
        ));

        return response()->json(['message' => 'Job created successfully', 'data' => $job], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $job = JobOffer::with(['user', 'sport'])->findOrFail($id);
        return response()->json($job, Response::HTTP_OK);
    }

    public function apply(Request $request, $id)
    {
        $job = JobOffer::findOrFail($id);

        $application = JobApplication::firstOrCreate([
            'job_offer_id' => $job->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Applied successfully', 'data' => $application], Response::HTTP_CREATED);
    }

    public function save(Request $request, $id)
    {
        $job = JobOffer::findOrFail($id);

        $savedId = SavedJob::firstOrCreate([
            'job_offer_id' => $job->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Job saved successfully'], Response::HTTP_OK);
    }

    public function unsave(Request $request, $id)
    {
        SavedJob::where('job_offer_id', $id)->where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Job unsaved successfully'], Response::HTTP_OK);
    }

    public function myApplications(Request $request)
    {
        $applications = JobApplication::with('jobOffer.company')->where('user_id', $request->user()->id)->latest()->get();
        return response()->json($applications, Response::HTTP_OK);
    }

    public function myOffers(Request $request)
    {
        $offers = JobOffer::where('user_id', $request->user()->id)->latest()->get();
        return response()->json($offers, Response::HTTP_OK);
    }

    public function mySaved(Request $request)
    {
        $saved = SavedJob::with('jobOffer')->where('user_id', $request->user()->id)->latest()->get();
        return response()->json($saved, Response::HTTP_OK);
    }
}
