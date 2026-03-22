<?php

namespace App\Policies;

use App\Models\GradeLevel;
use App\Models\User;

class GradeLevelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->hasRole('administrator');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GradeLevel $gradeLevel): bool
    {
        return $user->hasRole('administrator');
    }
}
