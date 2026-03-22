<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff', 'branch_manager']);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->ownsBranch($user, $payment);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['administrator', 'staff', 'branch_manager']);
    }

    /**
     * Financial records should only be changed by administrators.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $user->hasRole('administrator');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasRole('administrator');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->hasRole('administrator');
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->hasRole('administrator');
    }

    protected function ownsBranch(User $user, Payment $payment): bool
    {
        if ($user->hasAnyRole(['administrator', 'staff'])) {
            return true;
        }

        if ($user->hasRole('branch_manager') && $user->branch_id) {
            $branchId = $payment->account?->student?->branch_id;

            return (int) $branchId === (int) $user->branch_id;
        }

        return false;
    }
}
