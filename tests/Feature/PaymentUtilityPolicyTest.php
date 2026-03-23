<?php

use App\Models\PaymentUtility;
use App\Models\User;

it('allows administrators full access and staff read-only access to payment utilities', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    expect($admin->can('viewAny', PaymentUtility::class))->toBeTrue()
        ->and($admin->can('create', PaymentUtility::class))->toBeTrue()
        ->and($staff->can('viewAny', PaymentUtility::class))->toBeTrue()
        ->and($staff->can('create', PaymentUtility::class))->toBeFalse();
});
