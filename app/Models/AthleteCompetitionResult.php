<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteCompetitionResult extends Model
{
    use HasUlids;

    protected $fillable = [
        'profile_id',
        'competed_at',
        'competition',
        'mark',
        'placement',
        'official_url',
        'verification_status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'profile_id' => 'string',
            'competed_at' => 'date',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
