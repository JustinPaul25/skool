<?php

use App\Models\Grade;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('allows administrators and staff to manage grades', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    expect($admin->can('viewAny', Grade::class))->toBeTrue()
        ->and($admin->can('create', Grade::class))->toBeTrue()
        ->and($staff->can('viewAny', Grade::class))->toBeTrue()
        ->and($staff->can('create', Grade::class))->toBeTrue();
});

it('denies branch managers, students, and users without roles', function () {
    $branchManager = User::factory()->create();
    $branchManager->assignRole('branch_manager');

    $student = User::factory()->create();
    $student->assignRole('student');

    $plain = User::factory()->create();

    expect($branchManager->can('viewAny', Grade::class))->toBeFalse()
        ->and($student->can('viewAny', Grade::class))->toBeFalse()
        ->and($plain->can('viewAny', Grade::class))->toBeFalse();
});
