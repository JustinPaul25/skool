<?php

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRejected;
use App\Filament\Resources\EnrollmentApplications\EnrollmentApplicationResource;
use App\Filament\Resources\EnrollmentApplications\Pages\ListEnrollmentApplications;
use App\Models\Branch;
use App\Models\EnrollmentApplication;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('filament approve action dispatches EnrollmentApproved', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $studentUser = User::factory()->create();
    $studentUser->assignRole('student');

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

    $student = Student::query()->create([
        'student_id' => 'STU-FILAMENT-APPROVE',
        'user_id' => $studentUser->id,
        'branch_id' => $branch->id,
        'first_name' => 'Jane',
        'last_name' => 'Applicant',
        'middle_name' => null,
        'birth_date' => '2015-01-01',
        'gender' => 'female',
        'guardian_name' => 'Parent',
        'guardian_phone' => '555-0100',
        'email' => 'jane@example.com',
    ]);

    $application = EnrollmentApplication::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $grade->id,
        'branch_id' => $branch->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    Event::fake([EnrollmentApproved::class]);
    actingAs($staff);

    $livewireTest = Livewire::test(ListEnrollmentApplications::class);
    /** @var HasTable $livewireInstance */
    $livewireInstance = $livewireTest->instance();

    $table = Table::make($livewireInstance);
    EnrollmentApplicationResource::table($table);

    $approveAction = $table->getFlatRecordActions()['approve'] ?? null;
    expect($approveAction)->not->toBeNull();

    $approveAction->record($application)->data(['notes' => 'Welcome'])->call();

    Event::assertDispatched(EnrollmentApproved::class);

    $application->refresh();
    expect($application->status)->toBe('approved');
});

it('filament reject action dispatches EnrollmentRejected', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $studentUser = User::factory()->create();
    $studentUser->assignRole('student');

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

    $student = Student::query()->create([
        'student_id' => 'STU-FILAMENT-REJECT',
        'user_id' => $studentUser->id,
        'branch_id' => $branch->id,
        'first_name' => 'Jane',
        'last_name' => 'Applicant',
        'middle_name' => null,
        'birth_date' => '2015-01-01',
        'gender' => 'female',
        'guardian_name' => 'Parent',
        'guardian_phone' => '555-0100',
        'email' => 'jane@example.com',
    ]);

    $application = EnrollmentApplication::query()->create([
        'student_id' => $student->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $grade->id,
        'branch_id' => $branch->id,
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    Event::fake([EnrollmentRejected::class]);
    actingAs($staff);

    $livewireTest = Livewire::test(ListEnrollmentApplications::class);
    /** @var HasTable $livewireInstance */
    $livewireInstance = $livewireTest->instance();

    $table = Table::make($livewireInstance);
    EnrollmentApplicationResource::table($table);

    $rejectAction = $table->getFlatRecordActions()['reject'] ?? null;
    expect($rejectAction)->not->toBeNull();

    $rejectAction->record($application)->data(['rejection_notes' => 'Incomplete docs'])->call();

    Event::assertDispatched(EnrollmentRejected::class);

    $application->refresh();
    expect($application->status)->toBe('rejected');
    expect($application->notes)->toContain('Incomplete docs');
});
