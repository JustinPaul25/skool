<?php

use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function createPortalStudentUser(): array
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

it('shows the portal login page', function () {
    $this->get('/portal/login')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Portal/Auth/Login'));
});

it('redirects guests away from the portal dashboard to login', function () {
    $this->get('/portal/dashboard')
        ->assertRedirect(route('portal.login'));
});

it('logs in a student and redirects to the portal dashboard', function () {
    ['user' => $user] = createPortalStudentUser();

    $user->update([
        'email' => 'student@example.test',
        'password' => Hash::make('password'),
    ]);

    $this->post('/portal/login', [
        'email' => 'student@example.test',
        'password' => 'password',
    ])->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects non-student accounts at portal login', function () {
    $user = User::factory()->create([
        'email' => 'staff@example.test',
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('staff');

    $this->post('/portal/login', [
        'email' => 'staff@example.test',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('allows a student to visit portal dashboard when authenticated', function () {
    ['user' => $user] = createPortalStudentUser();

    $this->actingAs($user)
        ->get('/portal/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Portal/Dashboard'));
});

it('redirects staff away from the student portal when authenticated', function () {
    $user = User::factory()->create();
    $user->assignRole('staff');

    $this->actingAs($user)
        ->get('/portal/dashboard')
        ->assertRedirect(url('/admin'));
});

it('logs a student out of the portal', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)
        ->post('/portal/logout')
        ->assertRedirect(route('portal.login'));

    $this->assertGuest();
});
