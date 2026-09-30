<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToInstitute
{
    protected static function bootBelongsToInstitute(): void
    {
        static::addGlobalScope('institute', function (Builder $builder) {
            // hasUser() se user dobara DB se load nahi hota (recursion nahi)
            if (! Auth::hasUser()) {
                return;
            }

            $user = Auth::user();

            if ($user->institute_id !== null) {
                $builder->where($builder->getModel()->getTable() . '.institute_id', $user->institute_id);
            }
        });
    }
}