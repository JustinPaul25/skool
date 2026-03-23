<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchScope;

class EnrollmentPolicy
{
    use ChecksBranchScope;

    public function viewAny(User $user): bool
    {
        return $user->can('view enrollments');
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        if (! $user->can('view enrollments')) {
            return false;
        }

        return $this->branchMatchesManager($user, $enrollment->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->can('create enrollments');
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        if (! $user->can('update enrollments')) {
            return false;
        }

        return $this->branchMatchesManager($user, $enrollment->branch_id);
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        if (! $user->can('delete enrollments')) {
            return false;
        }

        return $this->branchMatchesManager($user, $enrollment->branch_id);
    }

    public function restore(User $user, Enrollment $enrollment): bool
    {
        if (! $user->can('delete enrollments')) {
            return false;
        }

        return $this->branchMatchesManager($user, $enrollment->branch_id);
    }

    public function forceDelete(User $user, Enrollment $enrollment): bool
    {
        if (! $user->can('delete enrollments')) {
            return false;
        }

        return $user->hasRole('administrator');
    }
}
