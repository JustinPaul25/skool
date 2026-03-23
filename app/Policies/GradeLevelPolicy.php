<?php

namespace App\Policies;

use App\Models\GradeLevel;
use App\Models\User;

class GradeLevelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view grade levels');
    }

    public function view(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('view grade levels');
    }

    public function create(User $user): bool
    {
        return $user->can('create grade levels');
    }

    public function update(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('update grade levels');
    }

    public function delete(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('delete grade levels');
    }

    public function restore(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('delete grade levels');
    }

    public function forceDelete(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->can('delete grade levels') && $user->hasRole('administrator');
    }
}
