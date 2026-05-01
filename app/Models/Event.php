<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'sport_id',
        'name',
        'event_date',
        'event_time',
        'location',
        'city',
        'country',
        'description',
        'photo_url',
        'organizer_name',
        'organizer_contact',
        'participants_count',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function savedBy(): HasMany
    {
        return $this->hasMany(SavedEvent::class);
    }
}
