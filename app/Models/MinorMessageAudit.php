<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinorMessageAudit extends Model
{
    use HasUlids;

    protected $fillable = [
        'conversation_id',
        'message_id',
        'adult_user_id',
        'minor_user_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'conversation_id' => 'integer',
            'message_id' => 'integer',
            'adult_user_id' => 'string',
            'minor_user_id' => 'string',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
