<?php

use App\Jobs\BulkReportCardJob;
use App\Jobs\GenerateReportCardJob;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Support\Facades\Bus;

it('dispatches a generate job per distinct enrolled student for the school year', function () {
    Bus::fake([GenerateReportCardJob::class]);

    $branch = Branch::query()->create([
        'name' => 'Main',
        'code' => 'M-'.uniqid(),
        'address' => '1 St',
        'phone' => '1',
        'email' => 'b@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $year = SchoolYear::query()->create([
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

    $s1 = Student::query()->create([
        'student_id' => 'S1-'.uniqid(),
        'branch_id' => $branch->id,
        'first_name' => 'A',
        'last_name' => 'One',
        'birth_date' => '2015-01-01',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
    ]);

    $s2 = Student::query()->create([
        'student_id' => 'S2-'.uniqid(),
        'branch_id' => $branch->id,
        'first_name' => 'B',
        'last_name' => 'Two',
        'birth_date' => '2015-01-01',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
    ]);

    foreach ([$s1, $s2] as $student) {
        Enrollment::query()->create([
            'student_id' => $student->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $gradeLevel->id,
            'branch_id' => $branch->id,
            'status' => 'enrolled',
        ]);
    }

    (new BulkReportCardJob($year->id))->handle();

    Bus::assertDispatchedTimes(GenerateReportCardJob::class, 2);
});

it('scopes bulk dispatch to a branch when branch id is set', function () {
    Bus::fake([GenerateReportCardJob::class]);

    $branchA = Branch::query()->create([
        'name' => 'A',
        'code' => 'A-'.uniqid(),
        'address' => '1 St',
        'phone' => '1',
        'email' => 'a@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $branchB = Branch::query()->create([
        'name' => 'B',
        'code' => 'B-'.uniqid(),
        'address' => '1 St',
        'phone' => '1',
        'email' => 'b@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $year = SchoolYear::query()->create([
        'name' => '2026-2027',
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31',
        'is_active' => false,
    ]);

    $gl = GradeLevel::query()->create([
        'name' => 'G2',
        'order' => 2,
        'branch_id' => $branchA->id,
    ]);

    $studentA = Student::query()->create([
        'student_id' => 'SA-'.uniqid(),
        'branch_id' => $branchA->id,
        'first_name' => 'A',
        'last_name' => 'Student',
        'birth_date' => '2015-01-01',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
    ]);

    $studentB = Student::query()->create([
        'student_id' => 'SB-'.uniqid(),
        'branch_id' => $branchB->id,
        'first_name' => 'B',
        'last_name' => 'Student',
        'birth_date' => '2015-01-01',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
    ]);

    foreach ([$studentA, $studentB] as $student) {
        Enrollment::query()->create([
            'student_id' => $student->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $gl->id,
            'branch_id' => $student->branch_id,
            'status' => 'enrolled',
        ]);
    }

    (new BulkReportCardJob($year->id, $branchA->id))->handle();

    Bus::assertDispatchedTimes(GenerateReportCardJob::class, 1);
    Bus::assertDispatched(GenerateReportCardJob::class, function (GenerateReportCardJob $job) use ($studentA, $year): bool {
        return $job->studentId === $studentA->id && $job->schoolYearId === $year->id;
    });
});
