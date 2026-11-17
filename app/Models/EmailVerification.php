<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'code',
    ];
}
