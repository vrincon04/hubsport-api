<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SportMetricDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sport_id' => $this->sport_id,
            'metric_group' => $this->metric_group,
            'key' => $this->key,
            'label' => $this->label,
            'unit' => $this->unit,
            'lower_is_better' => $this->lower_is_better,
            'sort_order' => $this->sort_order,
        ];
    }
}
