<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportsReference extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'reference_request_id',
        'author_id',
        'subject_user_id',
        'relationship_type',
        'body',
        'status',
        'is_suspicious',
        'suspicious_reason',
    ];

    protected $casts = [
        'is_suspicious' => 'boolean',
    ];

    public function request()
    {
        return $this->belongsTo(ReferenceRequest::class, 'reference_request_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function subject()
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}
