<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchScope;
use App\Policies\Concerns\DeniesStudentPanelAccess;

class GradePolicy
{
    use ChecksBranchScope;
    use DeniesStudentPanelAccess;

    public function viewAny(User $user): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('view grades');
    }

    public function view(User $user, Grade $grade): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        if (! $user->can('view grades')) {
            return false;
        }

        $grade->loadMissing('enrollment');

        return $this->branchMatchesManager($user, $grade->enrollment?->branch_id);
    }

    public function create(User $user): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('create grades');
    }

    public function update(User $user, Grade $grade): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        if (! $user->can('update grades')) {
            return false;
        }

        $grade->loadMissing('enrollment');

        return $this->branchMatchesManager($user, $grade->enrollment?->branch_id);
    }

    public function delete(User $user, Grade $grade): bool
    {
        if (! $user->can('delete grades')) {
            return false;
        }

        $grade->loadMissing('enrollment');

        return $this->branchMatchesManager($user, $grade->enrollment?->branch_id);
    }

    public function restore(User $user, Grade $grade): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $this->delete($user, $grade);
    }

    public function forceDelete(User $user, Grade $grade): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('delete grades') && $user->hasRole('administrator');
    }
}
