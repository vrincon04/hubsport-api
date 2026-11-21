<?php

namespace App\Http\Controllers\V1\Safety;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserReport;
use App\Support\RolePermissionPolicy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        RolePermissionPolicy::denyUnless(
            RolePermissionPolicy::canReviewReports($request->user()),
            'No autorizado para revisar reportes.'
        );

        $reports = UserReport::query()
            ->with(['reporter:id,name,email', 'target:id,name,email'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->query('priority')))
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'error' => null,
            'meta' => [
                'total' => $reports->total(),
                'page' => $reports->currentPage(),
                'limit' => $reports->perPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_user_id' => ['nullable', 'string', 'exists:users,id'],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'reason' => ['required', Rule::in(UserReport::REASONS)],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless(
            ! empty($data['target_user_id']) || ! empty($data['conversation_id']),
            422,
            'Debes indicar un usuario o una conversación'
        );

        if (! empty($data['target_user_id'])) {
            abort_unless(
                User::query()->find($data['target_user_id'])?->isVisibleTo($request->user()),
                404
            );
        }

        $priority = $data['reason'] === 'inappropriate_minor' ? 'immediate' : 'normal';

        $report = UserReport::query()->create([
            'reporter_id' => $request->user()->id,
            'target_user_id' => $data['target_user_id'] ?? null,
            'conversation_id' => $data['conversation_id'] ?? null,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'priority' => $priority,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'data' => $report,
            'error' => null,
            'meta' => [
                'priority' => $priority,
                'message' => $priority === 'immediate'
                    ? 'Reporte enviado a revisión inmediata'
                    : 'Reporte enviado',
            ],
        ], 201);
    }
}
