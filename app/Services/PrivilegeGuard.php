<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PrivilegeGuard
{
    
    public function canGrantPermissions(User $actor, array $permissionNames): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        $names = array_values(array_unique($permissionNames));
        $permissions = Permission::whereIn('name', $names)->get();

        if ($permissions->count() !== count($names)) {
            return false;  
        }

        foreach ($permissions as $permission) {
            if (! $permission->is_delegable || ! $actor->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     
     * @return list<string>
     */
    public function grantablePermissionNames(User $actor): array
    {
        return Permission::query()
            ->where('is_delegable', true)
            ->orderBy('name')
            ->get()
            ->filter(fn ($permission) => $actor->hasPermissionTo($permission))
            ->pluck('name')
            ->values()
            ->all();
    }
}