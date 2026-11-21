<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportMetricDefinition extends Model
{
    use HasUlids;

    protected $fillable = [
        'sport_id',
        'metric_group',
        'key',
        'label',
        'unit',
        'lower_is_better',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'sport_id' => 'string',
            'lower_is_better' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(AthleteMetricValue::class, 'definition_id');
    }
}
