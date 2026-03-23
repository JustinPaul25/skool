<?php

use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\get;

function seedStudentPortalGradesFixture(): array
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

    // Enrollment/grade relations are required for portal grades to render.
    $enrollment = Enrollment::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'branch_id' => $branch->id,
        'status' => 'enrolled',
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

    return compact('user');
}

it('redirects guests to login when accessing the student portal', function () {
    get('/portal/grades')
        ->assertRedirect(route('portal.login'));
});

it('authenticated student can view portal grades', function () {
    ['user' => $user] = seedStudentPortalGradesFixture();

    $this->actingAs($user)
        ->get('/portal/grades')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Portal/Grades/Index'));
});
