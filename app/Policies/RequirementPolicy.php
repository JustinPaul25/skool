<?php

namespace App\Policies;

use App\Models\Requirement;
use App\Models\User;
use App\Policies\Concerns\DeniesStudentPanelAccess;

class RequirementPolicy
{
    use DeniesStudentPanelAccess;

    public function viewAny(User $user): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('view requirements');
    }

    public function view(User $user, Requirement $requirement): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('view requirements');
    }

    public function create(User $user): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('create requirements');
    }

    public function update(User $user, Requirement $requirement): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('update requirements');
    }

    public function delete(User $user, Requirement $requirement): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('delete requirements');
    }

    public function restore(User $user, Requirement $requirement): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('delete requirements');
    }

    public function forceDelete(User $user, Requirement $requirement): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('delete requirements') && $user->hasRole('administrator');
    }
}
