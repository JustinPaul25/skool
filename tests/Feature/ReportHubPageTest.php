<?php

use App\Models\Branch;
use App\Models\User;

it('allows administrators and staff to open the report hub', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/admin/report-hub')
        ->assertSuccessful();
})->with([
    'administrator',
    'staff',
]);

it('allows branch managers to open the report hub', function () {
    $branch = Branch::query()->create([
        'name' => 'North',
        'code' => 'N-'.uniqid(),
        'address' => '1 St',
        'phone' => '1',
        'email' => 'n@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $user = User::factory()->create(['branch_id' => $branch->id]);
    $user->assignRole('branch_manager');

    $this->actingAs($user)
        ->get('/admin/report-hub')
        ->assertSuccessful();
});

it('forbids students from opening the report hub', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)
        ->get('/admin/report-hub')
        ->assertForbidden();
});
