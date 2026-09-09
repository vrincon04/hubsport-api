<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Para modelos que pertenecen a un usuario y se exponen en listados públicos.
 */
trait HidesDemoContent
{
    /**
     * El contenido de las cuentas demo solo existe para una sesión demo, así el
     * dataset de presentación no aparece en el feed de los usuarios reales.
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
    {
        if ($viewer?->is_demo) {
            return $query;
        }

        return $query->whereDoesntHave(
            'user',
            fn (Builder $owner) => $owner->where('is_demo', true)
        );
    }
}
