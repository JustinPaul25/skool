<?php

use App\Models\Account;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\Payment;
use App\Models\Requirement;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function seedPortalFullFixture(): array
{
    $branch = Branch::query()->create([
        'name' => 'Main',
        'code' => 'M-'.uniqid(),
        'is_active' => true,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'is_active' => true,
    ]);

    $gradeLevel = GradeLevel::query()->create([
        'name' => 'Grade 7',
        'order' => 7,
        'branch_id' => $branch->id,
    ]);

    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('student');

    $student = Student::query()->create([
        'student_id' => 'STU-'.uniqid(),
        'user_id' => $user->id,
        'branch_id' => $branch->id,
        'first_name' => 'Alex',
        'last_name' => 'Lee',
        'birth_date' => '2012-05-01',
        'guardian_name' => 'Parent',
        'guardian_phone' => '555',
    ]);

    $enrollment = Enrollment::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'branch_id' => $branch->id,
        'status' => 'enrolled',
    ]);

    $account = Account::query()->create([
        'student_id' => $student->id,
        'balance' => '150.00',
    ]);

    $subject = Subject::query()->create([
        'name' => 'Science',
        'code' => 'SCI-'.uniqid(),
        'grade_level_id' => $gradeLevel->id,
    ]);

    Grade::query()->create([
        'enrollment_id' => $enrollment->id,
        'subject_id' => $subject->id,
        'period' => Grade::PERIOD_Q1,
        'score' => 88,
        'remarks' => null,
        'graded_by' => null,
    ]);

    $payment = Payment::query()->create([
        'account_id' => $account->id,
        'enrollment_id' => $enrollment->id,
        'amount' => '500.00',
        'type' => 'tuition',
        'reference_no' => 'OR-TEST-'.uniqid(),
        'received_by' => $user->id,
        'paid_at' => now(),
        'notes' => null,
    ]);

    Requirement::query()->create([
        'name' => 'Birth certificate',
        'description' => 'Upload a copy',
        'is_required' => true,
        'grade_level_id' => null,
    ]);

    return compact('user', 'student', 'schoolYear', 'enrollment', 'account', 'payment', 'subject');
}

it('shows portal grades with subjects', function () {
    ['user' => $user] = seedPortalFullFixture();

    $this->actingAs($user)
        ->get('/portal/grades')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Portal/Grades/Index')
            ->has('subjects', 1));
});

it('shows portal payments with balance', function () {
    ['user' => $user] = seedPortalFullFixture();

    $this->actingAs($user)
        ->get('/portal/payments')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Portal/Payments/Index')
            ->where('balance', '150.00'));
});

it('allows a student to update profile phone', function () {
    ['user' => $user] = seedPortalFullFixture();

    $this->actingAs($user)
        ->patch('/portal/profile', [
            'phone' => '555-9999',
            'address' => '123 Test St',
        ])
        ->assertRedirect(route('portal.profile'));

    expect(Student::query()->where('user_id', $user->id)->first()?->phone)->toBe('555-9999');
});

it('lists document requirements for active enrollment', function () {
    ['user' => $user] = seedPortalFullFixture();

    $this->actingAs($user)
        ->get('/portal/documents')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Portal/Documents/Index')
            ->where('hasEnrollment', true)
            ->has('requirements', 1));
});

it('returns 404 for report card when none exists', function () {
    ['user' => $user, 'schoolYear' => $schoolYear] = seedPortalFullFixture();

    $this->actingAs($user)
        ->get('/portal/report-card/download?school_year_id='.$schoolYear->id)
        ->assertNotFound();
});
