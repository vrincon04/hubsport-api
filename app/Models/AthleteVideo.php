<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteVideo extends Model
{
    use HasUlids;

    protected $fillable = [
        'profile_id',
        'title',
        'video_url',
        'kind',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'profile_id' => 'string',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
