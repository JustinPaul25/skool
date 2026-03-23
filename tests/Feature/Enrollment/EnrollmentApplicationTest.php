<?php

use App\Events\EnrollmentApproved;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\EnrollmentApplication;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EnrollmentApprovedNotification;
use App\Services\EnrollmentApplicationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\post;

it('submits an application, approves it, creates enrollment, and sends the approved notification', function () {
    Notification::fake();

    $branch = Branch::query()->create([
        'name' => 'Main Campus',
        'code' => 'MAIN',
        'address' => '1 Test St',
        'phone' => '555',
        'email' => 'branch@test.com',
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

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $response = post(route('enrollment.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Applicant',
        'middle_name' => '',
        'birth_date' => '2018-05-01',
        'gender' => 'female',
        'address' => '123 Street',
        'phone' => '',
        'email' => 'jane.applicant@example.com',
        'guardian_name' => 'Parent Name',
        'guardian_phone' => '555-0100',
        'guardian_relationship' => 'Mother',
        'branch_id' => $branch->id,
        'grade_level_id' => $grade->id,
        'school_year_id' => $schoolYear->id,
        'notes' => 'Test note',
    ]);

    $response->assertRedirect(route('enrollment.thank-you'));

    $application = EnrollmentApplication::query()->latest()->first();
    expect($application)->not->toBeNull();

    $student = Student::query()->find($application->student_id);
    expect($student)->not->toBeNull();

    $studentUser = User::factory()->create();
    $studentUser->assignRole('student');
    $student->user_id = $studentUser->id;
    $student->save();

    app(EnrollmentApplicationService::class)->approve($application, $staff, 'Welcome');

    $application->refresh();

    expect($application->status)->toBe('approved');
    expect(Enrollment::query()->where('student_id', $student->id)->count())->toBe(1);

    Notification::assertSentTo($studentUser, EnrollmentApprovedNotification::class);
});

it('dispatches EnrollmentApproved when approving a submitted application', function () {
    Event::fake([EnrollmentApproved::class]);

    $branch = Branch::query()->create([
        'name' => 'Main Campus',
        'code' => 'MAIN',
        'address' => '1 Test St',
        'phone' => '555',
        'email' => 'branch@test.com',
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

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $response = post(route('enrollment.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Applicant',
        'middle_name' => '',
        'birth_date' => '2018-05-01',
        'gender' => 'female',
        'address' => '123 Street',
        'phone' => '',
        'email' => 'jane.applicant@example.com',
        'guardian_name' => 'Parent Name',
        'guardian_phone' => '555-0100',
        'guardian_relationship' => 'Mother',
        'branch_id' => $branch->id,
        'grade_level_id' => $grade->id,
        'school_year_id' => $schoolYear->id,
        'notes' => 'Test note',
    ]);

    $response->assertRedirect(route('enrollment.thank-you'));

    $application = EnrollmentApplication::query()->latest()->first();
    expect($application)->not->toBeNull();

    app(EnrollmentApplicationService::class)->approve($application, $staff, 'Welcome');

    Event::assertDispatched(EnrollmentApproved::class);

    $application->refresh();
    expect($application->status)->toBe('approved');
});
