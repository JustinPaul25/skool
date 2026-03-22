<?php

namespace App\Services;

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRejected;
use App\Models\Account;
use App\Models\Enrollment;
use App\Models\EnrollmentApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EnrollmentApplicationService
{
    public function approve(EnrollmentApplication $application, User $reviewer, ?string $notes = null): EnrollmentApplication
    {
        if (! in_array($application->status, ['submitted', 'under_review'], true)) {
            throw new InvalidArgumentException('Only submitted or under-review applications can be approved.');
        }

        if (! $application->student_id) {
            throw new InvalidArgumentException('Cannot approve without a linked student record.');
        }

        DB::transaction(function () use ($application, $reviewer, $notes): void {
            $application->refresh();

            $student = $application->student;
            if (! $student) {
                throw new InvalidArgumentException('Student record is missing.');
            }

            Enrollment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'school_year_id' => $application->school_year_id,
                ],
                [
                    'grade_level_id' => $application->grade_level_id,
                    'branch_id' => $application->branch_id,
                    'section_id' => null,
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ],
            );

            Account::firstOrCreate(
                ['student_id' => $student->id],
                ['balance' => 0],
            );

            $application->status = 'approved';
            $application->reviewed_by = $reviewer->id;

            if (filled($notes)) {
                $application->notes = $this->appendNote(
                    $application->notes,
                    'Approved',
                    $notes,
                );
            }

            $application->save();
        });

        $application->refresh();

        EnrollmentApproved::dispatch($application);

        return $application;
    }

    public function reject(EnrollmentApplication $application, User $reviewer, string $rejectionNotes): EnrollmentApplication
    {
        if (trim($rejectionNotes) === '') {
            throw new InvalidArgumentException('Rejection notes are required.');
        }

        if (! in_array($application->status, ['submitted', 'under_review'], true)) {
            throw new InvalidArgumentException('Only submitted or under-review applications can be rejected.');
        }

        DB::transaction(function () use ($application, $reviewer, $rejectionNotes): void {
            $application->refresh();

            $application->status = 'rejected';
            $application->reviewed_by = $reviewer->id;
            $application->notes = $this->appendNote(
                $application->notes,
                'Rejected',
                $rejectionNotes,
            );
            $application->save();
        });

        $application->refresh();

        EnrollmentRejected::dispatch($application);

        return $application;
    }

    protected function appendNote(?string $existing, string $label, string $text): string
    {
        $stamp = now()->toDateTimeString();
        $block = "[{$label} {$stamp}] {$text}";

        return trim(($existing ? $existing."\n\n" : '').$block);
    }
}
