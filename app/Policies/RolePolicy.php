<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view') && $this->inScope($user, $role);
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.update') && $this->manageable($user, $role);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.delete') && $this->manageable($user, $role);
    }

    
    public function assign(User $actor, Role $role, User $target): bool
    {
        if (! $actor->can('roles.assign')) {
            return false;
        }

        // Global roles
        if ($role->institute_id === null) {
            if (! $actor->can('global-roles.manage')) {
                return false;
            }

            if ($role->name === User::ROLE_SUPER_ADMIN) {
                return $actor->isSuperAdmin() && $target->institute_id === null;
            }

            if ($role->name === User::ROLE_INSTITUTE_ADMIN) {
                return $target->institute_id !== null;
            }

            return true;
        }

        
        return $role->institute_id === $target->institute_id
            && ($actor->isGlobalUser() || $actor->institute_id === $role->institute_id);
    }

    private function inScope(User $user, Role $role): bool
    {
        return $user->isGlobalUser() || $role->institute_id === $user->institute_id;
    }

    private function manageable(User $user, Role $role): bool
    {
        if ($role->is_protected) {
            return false;  
        }

        if ($role->institute_id === null) {
            return $user->can('global-roles.manage');
        }

        return $user->isGlobalUser() || $user->institute_id === $role->institute_id;
    }
}