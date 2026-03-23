<?php

use App\Models\User;

it('forbids student role from the admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows an administrator to open the admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});
