<?php

use App\Jobs\GenerateReportCardJob;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\ReportCardReadyNotification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

function seedReportCardScenario(): array
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
        'first_name' => 'Alex',
        'last_name' => 'Rivera',
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

    Grade::query()->create([
        'enrollment_id' => $enrollment->id,
        'subject_id' => $subject->id,
        'period' => Grade::PERIOD_Q1,
        'score' => 92.5,
        'remarks' => null,
        'graded_by' => null,
    ]);

    return compact('student', 'schoolYear', 'enrollment');
}

it('generates a pdf, stores media, and notifies the linked student user', function () {
    Notification::fake();

    $fixtures = seedReportCardScenario();
    $student = $fixtures['student'];
    $schoolYear = $fixtures['schoolYear'];

    $user = User::factory()->create();
    $student->update(['user_id' => $user->id]);

    Bus::dispatchSync(new GenerateReportCardJob($student->id, $schoolYear->id));

    $media = Media::query()->where('model_type', Student::class)
        ->where('model_id', $student->id)
        ->where('collection_name', 'report_cards')
        ->first();

    expect($media)->not->toBeNull()
        ->and($media->mime_type)->toBe('application/pdf');

    Notification::assertSentTo($user, ReportCardReadyNotification::class);
});

it('stores report media without notifying when no user is linked', function () {
    Notification::fake();

    $fixtures = seedReportCardScenario();
    $student = $fixtures['student'];
    $schoolYear = $fixtures['schoolYear'];

    Bus::dispatchSync(new GenerateReportCardJob($student->id, $schoolYear->id));

    expect(Media::query()->where('collection_name', 'report_cards')->count())->toBe(1);

    Notification::assertNothingSent();
});
