<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasUlids;
    protected $fillable = [
        'name',
        'code',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
        ];
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
