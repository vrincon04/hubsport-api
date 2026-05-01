<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceRequest extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'requester_id',
        'recipient_id',
        'relationship_type',
        'message',
        'status',
        'requester_confirmed_at',
        'recipient_confirmed_at',
        'ip_address',
    ];

    protected $casts = [
        'requester_confirmed_at' => 'datetime',
        'recipient_confirmed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function reference()
    {
        return $this->hasOne(SportsReference::class);
    }
}
