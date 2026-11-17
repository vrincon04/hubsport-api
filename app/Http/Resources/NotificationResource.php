<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payload = is_array($this->data) ? $this->data : [];
        $type = $payload['type'] ?? class_basename((string) $this->type);

        return [
            'id' => $this->id,
            'type' => $type,
            'title' => $payload['title'] ?? null,
            'body' => $payload['body'] ?? $payload['message'] ?? $payload['title'] ?? null,
            'from_user' => $payload['from_user'] ?? $payload['user'] ?? null,
            'related_id' => $payload['related_id']
                ?? $payload['post_id']
                ?? $payload['event_id']
                ?? $payload['connection_id']
                ?? null,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
