<?php

namespace App\Models;

use App\Support\PublicDiskUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_url',
        'type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected function mediaUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($value === null || $value === '') {
                    return $value;
                }
                if (preg_match('#^https?://#i', $value)) {
                    if (config('app.env') === 'production'
                        && str_contains($value, 'localhost')
                        && ($path = parse_url($value, PHP_URL_PATH))) {
                        return rtrim((string) config('filesystems.disks.public.url'), '/').$path;
                    }

                    return $value;
                }
                if (str_starts_with($value, '/storage/')) {
                    $base = rtrim((string) config('filesystems.disks.public.url'), '/');

                    return $base.substr($value, strlen('/storage'));
                }

                return PublicDiskUrl::forRelativePath($value);
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
