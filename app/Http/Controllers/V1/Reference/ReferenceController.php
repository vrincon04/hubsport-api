<?php

namespace App\Http\Controllers\V1\Reference;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ReferenceRequest;
use App\Models\SportsReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ReferenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $received = ReferenceRequest::with(['requester.profile.country', 'requester.profile.sport', 'requester.avatar', 'reference'])
            ->where('recipient_id', $userId)
            ->latest()
            ->get()
            ->map(fn (ReferenceRequest $referenceRequest) => $this->serializeRequest($referenceRequest));

        $sent = ReferenceRequest::with(['recipient.profile.country', 'recipient.profile.sport', 'recipient.avatar', 'reference'])
            ->where('requester_id', $userId)
            ->latest()
            ->get()
            ->map(fn (ReferenceRequest $referenceRequest) => $this->serializeRequest($referenceRequest));

        return response()->json([
            'data' => [
                'received' => $received,
                'sent' => $sent,
            ],
        ]);
    }

    public function publishedForUser(string $userId): JsonResponse
    {
        $references = SportsReference::with(['author.profile.country', 'author.profile.sport', 'author.avatar'])
            ->where('subject_user_id', $userId)
            ->where('status', 'published')
            ->latest()
            ->get()
            ->map(fn (SportsReference $reference) => $this->serializeReference($reference));

        return response()->json(['data' => $references]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'relationship_type' => 'required|string|max:80',
            'message' => 'nullable|string|max:1000',
        ]);

        abort_if($data['recipient_id'] === Auth::id(), Response::HTTP_UNPROCESSABLE_ENTITY, 'No puedes solicitarte una referencia a ti mismo.');

        $referenceRequest = ReferenceRequest::firstOrCreate(
            [
                'requester_id' => Auth::id(),
                'recipient_id' => $data['recipient_id'],
                'status' => 'pending',
            ],
            [
                'relationship_type' => $data['relationship_type'],
                'message' => $data['message'] ?? null,
                'ip_address' => $request->ip(),
            ],
        );

        return response()->json(['data' => $this->serializeRequest($referenceRequest->load(['recipient', 'requester']))], Response::HTTP_CREATED);
    }

    public function accept(string $id): JsonResponse
    {
        $referenceRequest = ReferenceRequest::where('recipient_id', Auth::id())->findOrFail($id);
        $referenceRequest->update(['status' => 'accepted']);

        return response()->json(['data' => $this->serializeRequest($referenceRequest->fresh(['requester', 'recipient', 'reference']))]);
    }

    public function reject(string $id): JsonResponse
    {
        $referenceRequest = ReferenceRequest::where('recipient_id', Auth::id())->findOrFail($id);
        $referenceRequest->update(['status' => 'rejected']);

        return response()->json(['data' => $this->serializeRequest($referenceRequest->fresh(['requester', 'recipient', 'reference']))]);
    }

    public function write(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'body' => 'required|string|min:20|max:3000',
        ]);

        $referenceRequest = ReferenceRequest::where('recipient_id', Auth::id())->findOrFail($id);
        abort_if($referenceRequest->status === 'rejected', Response::HTTP_UNPROCESSABLE_ENTITY, 'La solicitud fue rechazada.');

        $suspicion = $this->detectSuspicion($referenceRequest, $request);

        $reference = SportsReference::updateOrCreate(
            ['reference_request_id' => $referenceRequest->id],
            [
                'author_id' => Auth::id(),
                'subject_user_id' => $referenceRequest->requester_id,
                'relationship_type' => $referenceRequest->relationship_type,
                'body' => $data['body'],
                'status' => 'pending_confirmation',
                'is_suspicious' => $suspicion !== null,
                'suspicious_reason' => $suspicion,
            ],
        );

        $referenceRequest->update([
            'status' => 'pending_confirmation',
            'recipient_confirmed_at' => now(),
        ]);

        return response()->json(['data' => $this->serializeReference($reference->load('author'))], Response::HTTP_CREATED);
    }

    public function confirm(string $id): JsonResponse
    {
        $referenceRequest = ReferenceRequest::where('requester_id', Auth::id())->findOrFail($id);
        $reference = $referenceRequest->reference()->firstOrFail();

        $referenceRequest->update([
            'status' => 'completed',
            'requester_confirmed_at' => now(),
        ]);
        $reference->update(['status' => 'published']);

        return response()->json(['data' => $this->serializeReference($reference->fresh('author'))]);
    }

    private function detectSuspicion(ReferenceRequest $referenceRequest, Request $request): ?string
    {
        $sameIpCount = ReferenceRequest::where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($sameIpCount >= 4) {
            return 'Múltiples solicitudes de referencia desde la misma IP en 24 horas.';
        }

        $pairCount = SportsReference::where('author_id', Auth::id())
            ->where('subject_user_id', $referenceRequest->requester_id)
            ->count();

        if ($pairCount >= 2) {
            return 'Referencias repetidas entre los mismos usuarios.';
        }

        return null;
    }

    private function serializeRequest(ReferenceRequest $referenceRequest): array
    {
        return [
            'id' => $referenceRequest->id,
            'relationship_type' => $referenceRequest->relationship_type,
            'message' => $referenceRequest->message,
            'status' => $referenceRequest->status,
            'requester_confirmed_at' => $referenceRequest->requester_confirmed_at,
            'recipient_confirmed_at' => $referenceRequest->recipient_confirmed_at,
            'requester' => $referenceRequest->relationLoaded('requester') && $referenceRequest->requester ? new UserResource($referenceRequest->requester) : null,
            'recipient' => $referenceRequest->relationLoaded('recipient') && $referenceRequest->recipient ? new UserResource($referenceRequest->recipient) : null,
            'reference' => $referenceRequest->relationLoaded('reference') && $referenceRequest->reference ? $this->serializeReference($referenceRequest->reference) : null,
            'created_at' => $referenceRequest->created_at,
        ];
    }

    private function serializeReference(SportsReference $reference): array
    {
        return [
            'id' => $reference->id,
            'reference_request_id' => $reference->reference_request_id,
            'relationship_type' => $reference->relationship_type,
            'body' => $reference->body,
            'status' => $reference->status,
            'is_suspicious' => $reference->is_suspicious,
            'suspicious_reason' => $reference->suspicious_reason,
            'author' => $reference->relationLoaded('author') && $reference->author ? new UserResource($reference->author) : null,
            'created_at' => $reference->created_at,
        ];
    }
}
