<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\AthleteMetricValueResource;
use App\Models\AthleteCompetitionResult;
use App\Models\AthleteMetricValue;
use App\Models\AthleteVideo;
use App\Models\SportMetricDefinition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScoutMetricsController extends Controller
{
    public function metrics(Request $request, ?User $user = null)
    {
        $target = $this->resolveTarget($request, $user);
        $profile = $target->profile;
        if (! $profile) {
            return response()->json([
                'success' => true,
                'data' => [],
                'error' => null,
                'meta' => ['empty' => true, 'invite' => 'Agrega tu primera métrica verificable'],
            ]);
        }

        $values = AthleteMetricValue::query()
            ->with('definition')
            ->where('profile_id', $profile->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => AthleteMetricValueResource::collection($values),
            'error' => null,
            'meta' => [
                'empty' => $values->isEmpty(),
                'invite' => $values->isEmpty()
                    ? 'Agrega tu primera métrica verificable'
                    : null,
            ],
        ]);
    }

    public function upsertMetrics(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;
        abort_unless($profile, 422, 'Perfil requerido');
        abort_unless($profile->sport_id, 422, 'Deporte requerido');

        $payload = $request->validate([
            'metrics' => ['required', 'array', 'min:1', 'max:40'],
            'metrics.*.definition_id' => [
                'required',
                'string',
                'distinct',
                Rule::exists('sport_metric_definitions', 'id')
                    ->where(fn ($q) => $q->where('sport_id', $profile->sport_id)->where('is_active', true)),
            ],
            'metrics.*.value_numeric' => ['nullable', 'numeric', 'min:-999999', 'max:999999'],
            'metrics.*.value_text' => ['nullable', 'string', 'max:100'],
            'metrics.*.recorded_at' => ['nullable', 'date'],
            'metrics.*.source' => ['nullable', 'string', 'max:40'],
            'metrics.*.source_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $saved = [];
        DB::transaction(function () use ($payload, $profile, &$saved) {
            foreach ($payload['metrics'] as $row) {
                if (! isset($row['value_numeric']) && empty($row['value_text'])) {
                    abort(422, 'Cada métrica requiere value_numeric o value_text');
                }

                $definition = SportMetricDefinition::query()->findOrFail($row['definition_id']);
                $value = AthleteMetricValue::query()->updateOrCreate(
                    [
                        'profile_id' => $profile->id,
                        'definition_id' => $definition->id,
                    ],
                    [
                        'value_numeric' => $row['value_numeric'] ?? null,
                        'value_text' => $row['value_text'] ?? null,
                        'recorded_at' => $row['recorded_at'] ?? now()->toDateString(),
                        'source' => $row['source'] ?? 'self_reported',
                        'source_url' => $row['source_url'] ?? null,
                        'verification_status' => AthleteMetricValue::STATUS_SELF,
                    ]
                );
                $saved[] = $value->load('definition');
            }
        });

        return response()->json([
            'success' => true,
            'data' => AthleteMetricValueResource::collection(collect($saved)),
            'error' => null,
        ]);
    }

    public function competitionResults(Request $request, ?User $user = null)
    {
        $target = $this->resolveTarget($request, $user);
        $profile = $target->profile;
        $rows = $profile
            ? AthleteCompetitionResult::query()->where('profile_id', $profile->id)->orderByDesc('competed_at')->get()
            : collect();

        return response()->json(['success' => true, 'data' => $rows, 'error' => null]);
    }

    public function storeCompetitionResult(Request $request)
    {
        $profile = $request->user()->profile;
        abort_unless($profile, 422);

        $data = $request->validate([
            'competed_at' => ['required', 'date'],
            'competition' => ['required', 'string', 'max:150'],
            'mark' => ['nullable', 'string', 'max:80'],
            'placement' => ['nullable', 'string', 'max:40'],
            'official_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $row = AthleteCompetitionResult::query()->create([
            ...$data,
            'profile_id' => $profile->id,
            'verification_status' => AthleteMetricValue::STATUS_SELF,
        ]);

        return response()->json(['success' => true, 'data' => $row, 'error' => null], 201);
    }

    public function videos(Request $request, ?User $user = null)
    {
        $target = $this->resolveTarget($request, $user);
        $profile = $target->profile;
        $rows = $profile
            ? AthleteVideo::query()->where('profile_id', $profile->id)->latest()->get()
            : collect();

        return response()->json(['success' => true, 'data' => $rows, 'error' => null]);
    }

    public function storeVideo(Request $request)
    {
        $profile = $request->user()->profile;
        abort_unless($profile, 422);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'video_url' => ['required', 'url:http,https', 'max:255'],
            'kind' => ['nullable', Rule::in(['game', 'technique', 'mark'])],
        ]);

        $row = AthleteVideo::query()->create([
            'profile_id' => $profile->id,
            'title' => $data['title'],
            'video_url' => $data['video_url'],
            'kind' => $data['kind'] ?? 'game',
        ]);

        return response()->json(['success' => true, 'data' => $row, 'error' => null], 201);
    }

    public function destroyVideo(Request $request, string $video)
    {
        $profile = $request->user()->profile;
        abort_unless($profile, 422);

        $row = AthleteVideo::query()
            ->where('profile_id', $profile->id)
            ->where('id', $video)
            ->firstOrFail();
        $row->delete();

        return response()->json(['success' => true, 'data' => null, 'error' => null]);
    }

    /**
     * Escalate metric verification: coach/club → coach_verified; federation → federation_verified.
     */
    public function verifyMetric(Request $request, User $user, string $metricValue)
    {
        $actor = $request->user();
        abort_unless($actor && $user->isVisibleTo($actor), 404);

        $data = $request->validate([
            'verification_status' => [
                'required',
                Rule::in([
                    AthleteMetricValue::STATUS_COACH,
                    AthleteMetricValue::STATUS_FEDERATION,
                ]),
            ],
        ]);

        $actorType = $actor->profile?->profile_type;
        $status = $data['verification_status'];

        if ($status === AthleteMetricValue::STATUS_COACH) {
            abort_unless(
                in_array($actorType, ['coach', 'recruiter'], true),
                403,
                'Solo entrenadores o reclutadores pueden verificar a nivel club.'
            );
        }

        if ($status === AthleteMetricValue::STATUS_FEDERATION) {
            abort_unless(
                in_array($actorType, ['federation', 'event_organizer'], true),
                403,
                'Solo federación u organizador oficial puede verificar a nivel federación.'
            );
        }

        $profile = $user->profile;
        abort_unless($profile, 422);

        $value = AthleteMetricValue::query()
            ->where('profile_id', $profile->id)
            ->where('id', $metricValue)
            ->firstOrFail();

        $value->update(['verification_status' => $status]);

        return response()->json([
            'success' => true,
            'data' => new AthleteMetricValueResource($value->fresh()->load('definition')),
            'error' => null,
        ]);
    }

    private function resolveTarget(Request $request, ?User $user): User
    {
        $target = $user ?? $request->user();
        abort_unless($target && $target->isVisibleTo($request->user()), 404);

        return $target;
    }
}
