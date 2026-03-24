<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolYearRolloverJob implements ShouldQueue
{
    use Queueable;

    /**
     * Optionally force rollover for a specific school year.
     */
    public function __construct(public ?int $schoolYearId = null) {}

    public function handle(): void
    {
        $current = $this->schoolYearId
            ? SchoolYear::query()->find($this->schoolYearId)
            : SchoolYear::appCurrent();

        if (! $current instanceof SchoolYear) {
            Log::warning('SchoolYearRolloverJob: current school year not found');

            return;
        }

        $next = SchoolYear::query()
            ->when(
                filled($current->start_date),
                fn ($q) => $q->where('start_date', '>', $current->start_date),
                fn ($q) => $q->where('id', '>', $current->id),
            )
            ->orderBy('start_date')
            ->orderBy('id')
            ->first();

        if (! $next instanceof SchoolYear) {
            Log::warning('SchoolYearRolloverJob: next school year not found', [
                'school_year_id' => $current->id,
            ]);

            return;
        }

        DB::transaction(function () use ($current, $next): void {
            $enrollments = Enrollment::query()
                ->with(['gradeLevel'])
                ->where('school_year_id', $current->id)
                ->where('status', 'enrolled')
                ->get();

            foreach ($enrollments as $enrollment) {
                if (! $enrollment instanceof Enrollment) {
                    continue;
                }

                $currentGradeLevel = $enrollment->gradeLevel;

                if (! $currentGradeLevel) {
                    continue;
                }

                $nextOrder = ((int) $currentGradeLevel->order) + 1;

                if ($nextOrder < 1) {
                    continue;
                }

                $nextGradeLevel = GradeLevel::query()
                    ->where('order', $nextOrder)
                    ->where(function ($q) use ($enrollment): void {
                        $q->whereNull('branch_id')
                            ->orWhere('branch_id', $enrollment->branch_id);
                    })
                    ->orderByRaw('CASE WHEN branch_id = ? THEN 1 ELSE 0 END DESC', [$enrollment->branch_id])
                    ->first();

                if ($nextGradeLevel) {
                    Grade::query()->where('enrollment_id', $enrollment->id)->delete();

                    $enrollment->update([
                        'school_year_id' => $next->id,
                        'grade_level_id' => $nextGradeLevel->id,
                        'section_id' => null,
                        'status' => 'enrolled',
                        'enrolled_at' => now(),
                    ]);
                } else {
                    // No higher grade exists for this branch: mark as graduated and reset section.
                    $enrollment->update([
                        'section_id' => null,
                        'status' => 'graduated',
                        'enrolled_at' => now(),
                    ]);
                }
            }

            // Close current year and activate the next.
            SchoolYear::query()->where('is_active', true)->update(['is_active' => false]);
            $next->update(['is_active' => true]);
            $current->update(['is_active' => false]);
        });
    }
}
