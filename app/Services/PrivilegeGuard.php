<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PrivilegeGuard
{
    /**
     * Actor sirf wahi permissions de sakta hai jo delegable hain
     * aur jo khud actor ke paas hain. Super Admin sab de sakta hai.
     */
    public function canGrantPermissions(User $actor, array $permissionNames): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        $names = array_values(array_unique($permissionNames));
        $permissions = Permission::whereIn('name', $names)->get();

        if ($permissions->count() !== count($names)) {
            return false; // koi permission exist hi nahi karti
        }

        foreach ($permissions as $permission) {
            if (! $permission->is_delegable || ! $actor->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }
}