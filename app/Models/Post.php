<?php

namespace App\Models;

use App\Contracts\Like\Likeable;
use App\Traits\Like\HasLike;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\morphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model implements HasMedia, Likeable
{
    use SoftDeletes;
    use HasUlids;
    use HasFactory;
    use HasSlug;
    use HasLike;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'user_id',
        'status',
        'published_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gallery(): morphMany
    {
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', 'gallery');
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'timestamp',
        ];
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    /**
     * Register the media collections.
     *
     * @param Media|null $media
     * @return void
     */
    public function registerMediaCollections(?Media $media = null): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaConversion('video');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
