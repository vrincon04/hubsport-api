<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteMetricValue extends Model
{
    use HasUlids;

    public const STATUS_SELF = 'self_reported';

    public const STATUS_COACH = 'coach_verified';

    public const STATUS_FEDERATION = 'federation_verified';

    protected $fillable = [
        'profile_id',
        'definition_id',
        'value_numeric',
        'value_text',
        'recorded_at',
        'source',
        'source_url',
        'verification_status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'profile_id' => 'string',
            'definition_id' => 'string',
            'value_numeric' => 'decimal:4',
            'recorded_at' => 'date',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(SportMetricDefinition::class, 'definition_id');
    }
}
