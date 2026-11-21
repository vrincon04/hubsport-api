<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'sport_locked_at',
        'profile_type',
        'account_status',
        'guardian_email',
        'guardian_confirmed_at',
        'guardian_token',
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
        'height_cm',
        'weight_kg',
        'wingspan_cm',
        'laterality',
        'physical_extras',
        'position',
        'sport_level',
        'current_team',
        'organization_name',
        'rnc',
        'organization_document_url',
        'organization_website',
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
        'sport_locked_at' => 'datetime',
        'guardian_confirmed_at' => 'datetime',
        'verified_badge' => 'boolean',
        'height_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'wingspan_cm' => 'decimal:2',
        'physical_extras' => 'array',
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

    public function metricValues(): HasMany
    {
        return $this->hasMany(AthleteMetricValue::class);
    }

    public function competitionResults(): HasMany
    {
        return $this->hasMany(AthleteCompetitionResult::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(AthleteVideo::class);
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
