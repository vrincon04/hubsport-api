<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteMetricValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'definition_id' => $this->definition_id,
            'value_numeric' => $this->value_numeric,
            'value_text' => $this->value_text,
            'recorded_at' => $this->recorded_at?->toDateString(),
            'source' => $this->source,
            'source_url' => $this->source_url,
            'verification_status' => $this->verification_status,
            'definition' => new SportMetricDefinitionResource($this->whenLoaded('definition')),
        ];
    }
}
