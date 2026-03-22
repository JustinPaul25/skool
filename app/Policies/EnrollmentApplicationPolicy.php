<?php

namespace App\Policies;

use App\Models\EnrollmentApplication;
use App\Models\User;

class EnrollmentApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff', 'branch_manager']);
    }

    public function view(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        if ($user->hasAnyRole(['administrator', 'staff'])) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            return (int) $user->branch_id === (int) $enrollmentApplication->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff', 'branch_manager']);
    }

    public function update(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        return $this->view($user, $enrollmentApplication);
    }

    public function delete(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        return $this->view($user, $enrollmentApplication);
    }

    /**
     * Approve or bulk-approve an application (Filament table / bulk actions).
     */
    public function approve(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        if (! $this->view($user, $enrollmentApplication)) {
            return false;
        }

        return in_array($enrollmentApplication->status, ['submitted', 'under_review'], true);
    }

    /**
     * Reject an application from the Filament table.
     */
    public function reject(User $user, EnrollmentApplication $enrollmentApplication): bool
    {
        return $this->approve($user, $enrollmentApplication);
    }
}
