<?php

namespace App\Models;

use App\Contracts\Auth\MustVerifyOpt;
use App\Traits\Like\InteractsWithLike;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class User extends Authenticatable implements MustVerifyOpt, HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use HasUlids;
    use HasSlug;
    use InteractsWithMedia;
    use InteractsWithLike;
    use Notifiable;
    use \App\Traits\Auth\MustVerifyOpt;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * @return HasMany
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return MorphOne
     */
    public function avatar(): MorphOne
    {
        return $this->morphOne(Media::class , 'model')
            ->where('collection_name', 'avatar');
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class , 'sender_id');
    }

    public function userSettings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function userNotifications(): MorphMany
    {
        return $this->morphMany(Notification::class , 'notifiable');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(Connection::class);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Connection::class , 'connected_user_id')->where('status', 'accepted');
    }

    public function following(): HasMany
    {
        return $this->hasMany(Connection::class , 'user_id')->where('status', 'accepted');
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function getConnectionStatusWith(User $other): string
    {
        $connection = Connection::query()
            ->where(function ($q) use ($other) {
                $q->where('user_id', $this->id)->where('connected_user_id', $other->id);
            })
            ->orWhere(function ($q) use ($other) {
                $q->where('user_id', $other->id)->where('connected_user_id', $this->id);
            })
            ->first();

        if (!$connection) return 'none';
        if ($connection->status === 'accepted') return 'accepted';
        if ($connection->status === 'pending') {
            return $connection->user_id === $this->id ? 'pending_outgoing' : 'pending_incoming';
        }
        return 'none';
    }

    public function getSportsStats(): array
    {
        // Mocking some stats for the premium UI grid
        // Ideally these would come from the MatchResult table or a specialized UserStats table
        return [
            ['value' => '2024', 'label' => 'Temporada'],
            ['value' => (string)rand(1, 100), 'label' => 'Lugar'],
            ['value' => (string)rand(10, 500), 'label' => 'Pts'],
            ['value' => (string)$this->posts()->count() + 10, 'label' => 'Partidas'],
            ['value' => (string)rand(5, 50), 'label' => 'Victorias'],
            ['value' => (string)rand(0, 10), 'label' => 'Poles'],
        ];
    }
}