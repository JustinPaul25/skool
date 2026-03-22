<?php

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRejected;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\EnrollmentApplication;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Services\EnrollmentApplicationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('approves an application and creates enrollment and account', function () {
    Event::fake([EnrollmentApproved::class]);

    $branch = Branch::query()->create([
        'name' => 'Main',
        'code' => 'MAIN',
        'address' => 'X',
        'phone' => '1',
        'email' => 'b@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'is_active' => true,
    ]);

    $grade = GradeLevel::query()->create([
        'name' => 'Grade 1',
        'order' => 1,
        'branch_id' => null,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-001',
        'branch_id' => $branch->id,
        'first_name' => 'Test',
        'last_name' => 'Student',
        'middle_name' => null,
        'birth_date' => '2015-01-01',
        'gender' => 'male',
        'guardian_name' => 'Parent',
        'guardian_phone' => '123',
        'email' => 'student@example.com',
    ]);

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $application = EnrollmentApplication::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $grade->id,
        'branch_id' => $branch->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    app(EnrollmentApplicationService::class)->approve($application, $staff, 'Welcome');

    $application->refresh();

    expect($application->status)->toBe('approved')
        ->and((int) $application->reviewed_by)->toBe((int) $staff->id);

    expect(Enrollment::query()->where('student_id', $student->id)->count())->toBe(1)
        ->and(Account::query()->where('student_id', $student->id)->count())->toBe(1);

    Event::assertDispatched(EnrollmentApproved::class);
});

it('rejects an application with notes and dispatches event', function () {
    Event::fake([EnrollmentRejected::class]);

    $branch = Branch::query()->create([
        'name' => 'East',
        'code' => 'EAST',
        'address' => 'Y',
        'phone' => '2',
        'email' => 'e@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2026-2027',
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31',
        'is_active' => false,
    ]);

    $grade = GradeLevel::query()->create([
        'name' => 'Grade 2',
        'order' => 2,
        'branch_id' => null,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-002',
        'branch_id' => $branch->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'middle_name' => null,
        'birth_date' => '2016-01-01',
        'gender' => 'female',
        'guardian_name' => 'Guardian',
        'guardian_phone' => '456',
        'email' => 'jane@example.com',
    ]);

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $application = EnrollmentApplication::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $grade->id,
        'branch_id' => $branch->id,
        'status' => 'under_review',
        'submitted_at' => now(),
    ]);

    app(EnrollmentApplicationService::class)->reject($application, $staff, 'Incomplete documents');

    $application->refresh();

    expect($application->status)->toBe('rejected')
        ->and($application->notes)->toContain('Incomplete documents');

    Event::assertDispatched(EnrollmentRejected::class);
});
