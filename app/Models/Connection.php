<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'connected_user_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function connectedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'connected_user_id');
    }

    /**
     * The other party in this connection relative to the authenticated user.
     */
    public function peerFor(User $auth): User
    {
        return $this->user_id === $auth->id
            ? $this->connectedUser
            : $this->user;
    }

    public function involves(User $user): bool
    {
        return $this->user_id === $user->id
            || $this->connected_user_id === $user->id;
    }
}
