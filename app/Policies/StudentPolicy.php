<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchScope;

class StudentPolicy
{
    use ChecksBranchScope;

    public function viewAny(User $user): bool
    {
        return $user->can('view students');
    }

    public function view(User $user, Student $student): bool
    {
        if (! $user->can('view students')) {
            return false;
        }

        return $this->branchMatchesManager($user, $student->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->can('create students');
    }

    public function update(User $user, Student $student): bool
    {
        if (! $user->can('update students')) {
            return false;
        }

        return $this->branchMatchesManager($user, $student->branch_id);
    }

    public function delete(User $user, Student $student): bool
    {
        if (! $user->can('delete students')) {
            return false;
        }

        return $this->branchMatchesManager($user, $student->branch_id);
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->can('delete students');
    }

    public function forceDelete(User $user, Student $student): bool
    {
        return $user->can('delete students') && $user->hasRole('administrator');
    }
}
