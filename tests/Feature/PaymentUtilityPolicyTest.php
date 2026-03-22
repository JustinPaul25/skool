<?php

use App\Models\PaymentUtility;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('allows only administrators to manage payment utilities', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    expect($admin->can('viewAny', PaymentUtility::class))->toBeTrue()
        ->and($staff->can('viewAny', PaymentUtility::class))->toBeFalse()
        ->and($staff->can('create', PaymentUtility::class))->toBeFalse();
});
