<?php

namespace App\Policies;

use App\Models\Institute;
use App\Models\User;

class InstitutePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('institutes.view');
    }

    public function view(User $user, Institute $institute): bool
    {
        return $user->can('institutes.view');
    }

    public function create(User $user): bool
    {
        return $user->can('institutes.create');
    }

    public function update(User $user, Institute $institute): bool
    {
        return $user->can('institutes.update');
    }

    public function delete(User $user, Institute $institute): bool
    {
        return $user->can('institutes.delete');
    }

    public function toggleStatus(User $user, Institute $institute): bool
    {
        return $user->can('institutes.toggle-status');
    }
}