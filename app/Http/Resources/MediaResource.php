<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "size" => $this->size,
            "mime_type" => $this->mime_type,
            "url" => $this->getFullUrl(),
        ];
    }
}
