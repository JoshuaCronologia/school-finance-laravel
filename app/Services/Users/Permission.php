<?php

namespace App\Services\Users;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * Exclude specific permissions from the list.
     */
    public function scopeAllowed($query)
    {
        return $query;
    }
}
