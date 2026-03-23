<?php

namespace App\Policies;

use App\Models\PaymentUtility;
use App\Models\User;

class PaymentUtilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view payment utilities');
    }

    public function view(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->can('view payment utilities');
    }

    public function create(User $user): bool
    {
        return $user->can('create payment utilities');
    }

    public function update(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->can('update payment utilities');
    }

    public function delete(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->can('delete payment utilities');
    }

    public function restore(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->can('delete payment utilities');
    }

    public function forceDelete(User $user, PaymentUtility $paymentUtility): bool
    {
        return $user->can('delete payment utilities') && $user->hasRole('administrator');
    }
}
