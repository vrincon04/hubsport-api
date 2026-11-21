<?php

namespace App\Http\Controllers\V1\Chat;

use App\Http\Controllers\Controller;
use App\Mail\TutorMessageCopyMail;
use App\Models\Conversation;
use App\Models\MinorMessageAudit;
use App\Support\AgePolicy;
use App\Support\RolePermissionPolicy;
use App\Support\ScamDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public function index(Conversation $conversation): JsonResponse
    {
        if (! $this->userBelongsToConversation($conversation, request()->user()->id)) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $messages = $conversation->messages()->with('sender')->oldest()->get();

        return response()->json($messages);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        if (! $this->userBelongsToConversation($conversation, $request->user()->id)) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        RolePermissionPolicy::denyUnless(
            RolePermissionPolicy::canWriteContent($request->user()),
            'Tu cuenta aún no puede enviar mensajes.'
        );

        $validated = $request->validate([
            'text' => 'required_without:content|string|max:2000',
            'content' => 'required_without:text|string|max:2000',
        ]);
        $text = $validated['text'] ?? $validated['content'];

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'text' => $text,
        ]);

        $this->auditAdultToMinor($request, $conversation, $message->id, $text);

        $scam = ScamDetector::mentionsMoney($text);

        return response()->json([
            'success' => true,
            'data' => $message,
            'error' => null,
            'meta' => [
                'scam_warning' => $scam,
                'scam_message' => $scam ? ScamDetector::warningMessage() : null,
            ],
        ], 201);
    }

    private function auditAdultToMinor(
        Request $request,
        Conversation $conversation,
        int $messageId,
        string $text
    ): void {
        $sender = $request->user()->loadMissing('profile');
        $senderIsMinor = AgePolicy::isMinor($sender->profile?->birth_date);
        if ($senderIsMinor) {
            return;
        }

        $participants = $conversation->users()->with('profile')->get();
        foreach ($participants as $participant) {
            if ($participant->id === $sender->id) {
                continue;
            }
            if (! AgePolicy::isMinor($participant->profile?->birth_date)) {
                continue;
            }

            MinorMessageAudit::query()->create([
                'conversation_id' => $conversation->id,
                'message_id' => $messageId,
                'adult_user_id' => $sender->id,
                'minor_user_id' => $participant->id,
            ]);

            $guardianEmail = $participant->profile?->guardian_email;
            if ($guardianEmail && filter_var($guardianEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($guardianEmail)->send(
                    new TutorMessageCopyMail($participant, $sender, $text)
                );
            }
        }
    }

    private function userBelongsToConversation(Conversation $conversation, string $userId): bool
    {
        return $conversation->users()
            ->where('users.id', $userId)
            ->exists();
    }
}
