<?php

namespace App\Policies;

use App\Models\User;

class InstituteAdminPolicy
{
    public function manage(User $user): bool
    {
        return $user->can('institute-admins.manage') && $user->isGlobalUser();
    }

   
    public function manageTarget(User $user, User $target): bool
    {
        return $this->manage($user)
            && $target->institute_id !== null
            && $target->hasRole(User::ROLE_INSTITUTE_ADMIN);
    }
}