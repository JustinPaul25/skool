<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksBranchScope;
use App\Policies\Concerns\DeniesStudentPanelAccess;

class PaymentPolicy
{
    use ChecksBranchScope;
    use DeniesStudentPanelAccess;

    public function viewAny(User $user): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('view payments');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        if (! $user->can('view payments')) {
            return false;
        }

        $branchId = $payment->account?->student?->branch_id;

        return $this->branchMatchesManager($user, $branchId);
    }

    public function create(User $user): bool
    {
        return $user->can('create payments');
    }

    public function update(User $user, Payment $payment): bool
    {
        if (! $user->can('update payments')) {
            return false;
        }

        $branchId = $payment->account?->student?->branch_id;

        return $this->branchMatchesManager($user, $branchId);
    }

    public function delete(User $user, Payment $payment): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        if (! $user->can('delete payments')) {
            return false;
        }

        $branchId = $payment->account?->student?->branch_id;

        return $this->branchMatchesManager($user, $branchId);
    }

    public function restore(User $user, Payment $payment): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $this->delete($user, $payment);
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        if ($this->isStudent($user)) {
            return false;
        }

        return $user->can('delete payments') && $user->hasRole('administrator');
    }
}
