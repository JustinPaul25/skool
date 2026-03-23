<?php

use App\Jobs\GradeImportJob;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\GradeImportCompletedNotification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

function seedGradeImportFixtures(): array
{
    $branch = Branch::query()->create([
        'name' => 'Main Campus',
        'code' => 'BR-'.uniqid(),
        'is_active' => true,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-03-31',
        'is_active' => true,
    ]);

    $gradeLevel = GradeLevel::query()->create([
        'name' => 'Grade 1',
        'order' => 1,
        'branch_id' => $branch->id,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-'.uniqid(),
        'branch_id' => $branch->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'birth_date' => '2015-01-01',
        'guardian_name' => 'Parent',
        'guardian_phone' => '555-0100',
    ]);

    $subject = Subject::query()->create([
        'name' => 'Mathematics',
        'code' => 'MATH-'.uniqid(),
        'grade_level_id' => $gradeLevel->id,
    ]);

    $enrollment = Enrollment::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'branch_id' => $branch->id,
        'status' => 'enrolled',
    ]);

    return compact('enrollment', 'subject');
}

it('imports grades from csv and notifies the user', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $user->assignRole('staff');

    Notification::fake();

    $fixtures = seedGradeImportFixtures();
    $enrollment = $fixtures['enrollment'];
    $subject = $fixtures['subject'];

    $csv = "enrollment_id,subject_id,period,score,remarks\n";
    $csv .= "{$enrollment->id},{$subject->id},q1,88.5,Good work\n";

    $path = 'grade-imports/test.csv';
    Storage::disk('local')->put($path, $csv);

    Bus::dispatchSync(new GradeImportJob($user->id, $path));

    $grade = Grade::query()->where('enrollment_id', $enrollment->id)
        ->where('subject_id', $subject->id)
        ->where('period', 'q1')
        ->first();

    expect($grade)->not->toBeNull()
        ->and((float) $grade->score)->toBe(88.5)
        ->and($grade->remarks)->toBe('Good work')
        ->and($grade->graded_by)->toBe($user->id);

    Notification::assertSentTo($user, GradeImportCompletedNotification::class);
});

it('records validation errors for bad rows', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $user->assignRole('staff');

    Notification::fake();

    $fixtures = seedGradeImportFixtures();
    $enrollment = $fixtures['enrollment'];
    $subject = $fixtures['subject'];

    $csv = "enrollment_id,subject_id,period,score,remarks\n";
    $csv .= "{$enrollment->id},{$subject->id},q1,150,Bad score\n";

    $path = 'grade-imports/bad.csv';
    Storage::disk('local')->put($path, $csv);

    Bus::dispatchSync(new GradeImportJob($user->id, $path));

    expect(Grade::query()->count())->toBe(0);

    Notification::assertSentTo($user, GradeImportCompletedNotification::class);
});
