<?php

namespace App\Policies;

use App\Models\PaymentUtility;
use App\Models\User;

class PaymentUtilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('administrator');
    }

    public function view(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->hasRole('administrator');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('administrator');
    }

    public function update(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->hasRole('administrator');
    }

    public function delete(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->hasRole('administrator');
    }

    public function restore(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->hasRole('administrator');
    }

    public function forceDelete(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->hasRole('administrator');
    }
}
