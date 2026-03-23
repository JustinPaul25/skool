<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view branches');
    }

    public function view(User $user, Branch $branch): bool
    {
        if (! $user->can('view branches')) {
            return false;
        }

        if ($user->hasRole('branch_manager')) {
            return (int) $user->branch_id === (int) $branch->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('create branches');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->can('update branches');
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->can('delete branches');
    }

    public function restore(User $user, Branch $branch): bool
    {
        return $user->can('delete branches');
    }

    public function forceDelete(User $user, Branch $branch): bool
    {
        return $user->can('delete branches');
    }
}
