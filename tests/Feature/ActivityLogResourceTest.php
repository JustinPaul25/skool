<?php

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

it('allows administrators to view the activity log list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    Activity::query()->create([
        'description' => 'Test activity',
        'event' => 'created',
        'subject_type' => null,
        'subject_id' => null,
    ]);

    $this->actingAs($admin)
        ->get('/admin/activity-logs')
        ->assertSuccessful();
});

it('forbids staff from viewing the activity log list', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->actingAs($staff)
        ->get('/admin/activity-logs')
        ->assertForbidden();
});

it('forbids branch managers from viewing the activity log list', function () {
    $manager = User::factory()->create();
    $manager->assignRole('branch_manager');

    $this->actingAs($manager)
        ->get('/admin/activity-logs')
        ->assertForbidden();
});
