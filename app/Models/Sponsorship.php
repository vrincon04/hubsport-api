<?php

namespace App\Models;

use App\Models\Concerns\HidesDemoContent;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsorship extends Model
{
    use HasFactory;
    use HasUlids;
    use HidesDemoContent;

    protected $fillable = [
        'user_id',
        'sport_id',
        'brand_name',
        'sponsorship_type',
        'requirements',
        'benefits',
        'contact',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }
}
