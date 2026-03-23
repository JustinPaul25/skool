<?php

namespace App\Jobs;

use App\Models\Enrollment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BulkReportCardJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $schoolYearId,
        public ?int $branchId = null,
    ) {}

    public function handle(): void
    {
        $studentIds = Enrollment::query()
            ->where('school_year_id', $this->schoolYearId)
            ->where('status', 'enrolled')
            ->when($this->branchId, fn ($q) => $q->where('branch_id', $this->branchId))
            ->pluck('student_id')
            ->unique()
            ->values();

        $count = 0;

        foreach ($studentIds as $studentId) {
            GenerateReportCardJob::dispatch((int) $studentId, $this->schoolYearId);
            $count++;
        }

        Log::info('Bulk report card jobs dispatched.', [
            'school_year_id' => $this->schoolYearId,
            'branch_id' => $this->branchId,
            'count' => $count,
        ]);
    }
}
