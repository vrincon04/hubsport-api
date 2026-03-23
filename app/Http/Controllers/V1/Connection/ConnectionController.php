<?php

namespace App\Http\Controllers\V1\Connection;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConnectionResource;
use App\Models\Connection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ConnectionController extends Controller
{
    private function loadUserRelations(): array
    {
        return [
            'user.profile',
            'user.avatar',
            'connectedUser.profile',
            'connectedUser.avatar',
        ];
    }

    /**
     * List accepted connections (friends) for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $connections = Connection::query()
            ->where('status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere('connected_user_id', $userId);
            })
            ->with($this->loadUserRelations())
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Connections list',
            'data' => ConnectionResource::collection($connections),
        ]);
    }

    /**
     * Pending requests where the current user is the recipient.
     */
    public function incoming(Request $request): JsonResponse
    {
        $connections = Connection::query()
            ->where('connected_user_id', $request->user()->id)
            ->where('status', 'pending')
            ->with(['user.profile', 'user.avatar'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Incoming connection requests',
            'data' => ConnectionResource::collection($connections),
        ]);
    }

    /**
     * Pending requests sent by the current user.
     */
    public function outgoing(Request $request): JsonResponse
    {
        $connections = Connection::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->with(['connectedUser.profile', 'connectedUser.avatar'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Outgoing connection requests',
            'data' => ConnectionResource::collection($connections),
        ]);
    }

    /**
     * Send a connection request (or re-open a previously rejected one from the same sender).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'connected_user_id' => ['required', 'string', 'exists:users,id'],
        ]);

        $sender = $request->user();
        $recipientId = $validated['connected_user_id'];

        if ($recipientId === $sender->id) {
            return response()->json(['message' => 'Cannot connect to yourself.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $reversePending = Connection::query()
            ->where('user_id', $recipientId)
            ->where('connected_user_id', $sender->id)
            ->where('status', 'pending')
            ->first();

        if ($reversePending) {
            return response()->json([
                'message' => 'This user already sent you a request. Accept it from incoming requests.',
                'data' => ['connection_id' => $reversePending->id],
            ], Response::HTTP_CONFLICT);
        }

        $existing = Connection::query()
            ->where('user_id', $sender->id)
            ->where('connected_user_id', $recipientId)
            ->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return response()->json(['message' => 'You are already connected with this user.'], Response::HTTP_CONFLICT);
            }
            if ($existing->status === 'pending') {
                return response()->json(['message' => 'Request already sent.'], Response::HTTP_CONFLICT);
            }
            if ($existing->status === 'rejected') {
                $existing->update(['status' => 'pending']);

                return response()->json([
                    'message' => 'Connection request sent.',
                    'data' => new ConnectionResource($existing->load($this->loadUserRelations())),
                ], Response::HTTP_OK);
            }
        }

        $connection = DB::transaction(function () use ($sender, $recipientId) {
            return Connection::create([
                'user_id' => $sender->id,
                'connected_user_id' => $recipientId,
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'message' => 'Connection request sent.',
            'data' => new ConnectionResource($connection->load($this->loadUserRelations())),
        ], Response::HTTP_CREATED);
    }

    /**
     * Accept an incoming pending request (recipient only).
     */
    public function accept(Request $request, Connection $connection): JsonResponse
    {
        $user = $request->user();

        if ($connection->connected_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        if ($connection->status !== 'pending') {
            return response()->json(['message' => 'This request is no longer pending.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $connection->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'Connection accepted.',
            'data' => new ConnectionResource($connection->fresh()->load($this->loadUserRelations())),
        ]);
    }

    /**
     * Reject an incoming pending request (recipient only).
     */
    public function reject(Request $request, Connection $connection): JsonResponse
    {
        $user = $request->user();

        if ($connection->connected_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        if ($connection->status !== 'pending') {
            return response()->json(['message' => 'This request is no longer pending.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $connection->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Connection rejected.',
            'data' => new ConnectionResource($connection->fresh()->load($this->loadUserRelations())),
        ]);
    }

    /**
     * Cancel an outgoing pending request, remove rejected row, or unfriend (delete accepted).
     */
    public function destroy(Request $request, Connection $connection): JsonResponse
    {
        $user = $request->user();

        if (! $connection->involves($user)) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        if ($connection->status === 'pending' && $connection->user_id === $user->id) {
            $connection->delete();

            return response()->json(['message' => 'Request cancelled.']);
        }

        if ($connection->status === 'pending' && $connection->connected_user_id === $user->id) {
            $connection->delete();

            return response()->json(['message' => 'Request dismissed.']);
        }

        if ($connection->status === 'accepted') {
            $connection->delete();

            return response()->json(['message' => 'Connection removed.']);
        }

        if ($connection->status === 'rejected') {
            $connection->delete();

            return response()->json(['message' => 'Connection record removed.']);
        }

        return response()->json(['message' => 'Cannot delete this connection.'], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
