<?php

use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createStudentChannelContext(): array
{
    $branch = Branch::query()->create([
        'name' => 'Test Branch',
        'code' => 'TB-'.uniqid(),
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('student');

    $student = Student::query()->create([
        'student_id' => 'STU-'.uniqid(),
        'user_id' => $user->id,
        'branch_id' => $branch->id,
        'first_name' => 'Portal',
        'last_name' => 'Student',
        'birth_date' => '2010-01-01',
        'guardian_name' => 'Guardian',
        'guardian_phone' => '555-0100',
    ]);

    return compact('user', 'student', 'branch');
}

it('authorizes a student for their private student channel', function () {
    ['user' => $user, 'student' => $student] = createStudentChannelContext();

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-student.'.$student->id,
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['auth']);
});

it('denies a student for another students private channel', function () {
    ['user' => $user, 'branch' => $branch] = createStudentChannelContext();

    $other = Student::query()->create([
        'student_id' => 'STU-'.uniqid(),
        'user_id' => null,
        'branch_id' => $branch->id,
        'first_name' => 'Other',
        'last_name' => 'Student',
        'birth_date' => '2010-01-02',
        'guardian_name' => 'Guardian',
        'guardian_phone' => '555-0200',
    ]);

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-student.'.$other->id,
        ])
        ->assertForbidden();
});

it('authorizes staff for a student private channel', function () {
    ['student' => $student] = createStudentChannelContext();

    $staff = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $staff->assignRole('staff');

    $this->actingAs($staff)
        ->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-student.'.$student->id,
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['auth']);
});
