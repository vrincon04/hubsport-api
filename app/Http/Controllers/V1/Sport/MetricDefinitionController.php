<?php

namespace App\Http\Controllers\V1\Sport;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportMetricDefinitionResource;
use App\Models\Sport;
use App\Models\SportMetricDefinition;
use Illuminate\Http\Request;

class MetricDefinitionController extends Controller
{
    public function index(Request $request, Sport $sport)
    {
        $definitions = SportMetricDefinition::query()
            ->where('sport_id', $sport->id)
            ->where('is_active', true)
            ->orderBy('metric_group')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => SportMetricDefinitionResource::collection($definitions),
            'error' => null,
        ]);
    }
}
