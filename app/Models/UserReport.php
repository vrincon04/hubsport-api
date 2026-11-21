<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    use HasUlids;

    public const REASONS = [
        'impersonation',
        'money_request',
        'inappropriate_minor',
        'fake_profile',
        'offensive',
    ];

    protected $fillable = [
        'reporter_id',
        'target_user_id',
        'conversation_id',
        'reason',
        'details',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'reporter_id' => 'string',
            'target_user_id' => 'string',
            'conversation_id' => 'integer',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
