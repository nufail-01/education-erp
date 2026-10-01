<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('teachers.view');
    }

    
    public function view(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.view')
            && ($user->isGlobalUser() || $this->sameInstitute($user, $teacher));
    }

    public function create(User $user): bool
    {
        return $user->can('teachers.create') && ! $user->isGlobalUser();
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.update') && $this->sameInstitute($user, $teacher);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->can('teachers.delete') && $this->sameInstitute($user, $teacher);
    }

    private function sameInstitute(User $user, Teacher $teacher): bool
    {
        return $user->institute_id !== null && $user->institute_id === $teacher->institute_id;
    }
}