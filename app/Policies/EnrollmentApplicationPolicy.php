<?php

namespace App\Policies;

use App\Models\EnrollmentApplication;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchScope;

class EnrollmentApplicationPolicy
{
    use ChecksBranchScope;

    public function viewAny(User $user): bool
    {
        return $user->can('view enrollment applications');
    }

    public function view(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        if (! $user->can('view enrollment applications')) {
            return false;
        }

        return $this->branchMatchesManager($user, $enrollmentApplication->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->can('create enrollment applications');
    }

    public function update(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        if (! $user->can('update enrollment applications')) {
            return false;
        }

        return $this->branchMatchesManager($user, $enrollmentApplication->branch_id);
    }

    public function delete(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        if (! $user->can('delete enrollment applications')) {
            return false;
        }

        return $this->branchMatchesManager($user, $enrollmentApplication->branch_id);
    }

    public function approve(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        if (! $this->view($user, $enrollmentApplication)) {
            return false;
        }

        if (! $user->can('review enrollment applications')) {
            return false;
        }

        return in_array($enrollmentApplication->status, ['submitted', 'under_review'], true);
    }

    public function reject(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        return $this->approve($user, $enrollmentApplication);
    }
}
