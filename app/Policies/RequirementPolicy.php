<?php

namespace App\Policies;

use App\Models\Requirement;
use App\Models\User;

class RequirementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function view(User $user, Requirement $requirement): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function update(User $user, Requirement $requirement): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function delete(User $user, Requirement $requirement): bool
    {
        return $user->hasAnyRole(['administrator', 'staff']);
    }

    public function restore(User $user, Requirement $requirement): bool
    {
        return $user->hasRole('administrator');
    }

    public function forceDelete(User $user, Requirement $requirement): bool
    {
        return $user->hasRole('administrator');
    }
}
