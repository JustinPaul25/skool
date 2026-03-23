<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksBranchScope
{
    protected function branchMatchesManager(User $user, ?int $branchId): bool
    {
        if (! $user->hasRole('branch_manager')) {
            return true;
        }

        return $user->branch_id !== null && (int) $user->branch_id === (int) $branchId;
    }
}
