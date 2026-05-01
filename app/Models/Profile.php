<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'country_id',
        'sport_id',
        'profile_type',
        'first_name',
        'last_name',
        'phone_number',
        'phone_verified_at',
        'verification_status',
        'verified_at',
        'verified_badge',
        'city',
        'bio',
        'birth_date',
        'position',
        'sport_level',
        'current_team',
        'social_links',
        'stats',
        'experience',
        'achievements',
        'education',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'birth_date' => 'datetime:Y-m-d',
        'phone_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'verified_badge' => 'boolean',
        'social_links' => 'array',
        'stats' => 'array',
        'experience' => 'array',
        'achievements' => 'array',
        'education' => 'array',
    ];

    /**
     * Relationship of user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get the user's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => ucfirst("{$attributes['first_name']} {$attributes['last_name']}"),
        );
    }
}
