<?php

use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

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

it('allows branch managers for their branch only', function () {
    $branch = Branch::query()->create([
        'name' => 'North',
        'code' => 'N-'.uniqid(),
        'address' => '1',
        'phone' => '1',
        'email' => 'n@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $otherBranch = Branch::query()->create([
        'name' => 'South',
        'code' => 'S-'.uniqid(),
        'address' => '1',
        'phone' => '1',
        'email' => 's@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'is_active' => true,
    ]);

    $gradeLevel = GradeLevel::query()->create([
        'name' => 'G1',
        'order' => 1,
        'branch_id' => $branch->id,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-GP-1',
        'branch_id' => $branch->id,
        'first_name' => 'A',
        'last_name' => 'B',
        'birth_date' => '2015-01-01',
        'gender' => 'male',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
    ]);

    $enrollment = Enrollment::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'branch_id' => $branch->id,
        'status' => 'enrolled',
    ]);

    $subject = Subject::query()->create([
        'name' => 'Math',
        'code' => 'M-'.uniqid(),
        'grade_level_id' => $gradeLevel->id,
    ]);

    $grade = Grade::query()->create([
        'enrollment_id' => $enrollment->id,
        'subject_id' => $subject->id,
        'period' => Grade::PERIOD_Q1,
        'score' => 90,
    ]);

    $manager = User::factory()->create(['branch_id' => $branch->id]);
    $manager->assignRole('branch_manager');

    $wrongManager = User::factory()->create(['branch_id' => $otherBranch->id]);
    $wrongManager->assignRole('branch_manager');

    expect($manager->can('viewAny', Grade::class))->toBeTrue()
        ->and($manager->can('view', $grade))->toBeTrue()
        ->and($wrongManager->can('view', $grade))->toBeFalse();
});

it('denies students and users without roles', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $plain = User::factory()->create();

    expect($student->can('viewAny', Grade::class))->toBeFalse()
        ->and($plain->can('viewAny', Grade::class))->toBeFalse();
});
