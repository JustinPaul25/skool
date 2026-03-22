<?php

namespace App\Jobs;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Notifications\ReportCardReadyNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateReportCardJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $studentId,
        public int $schoolYearId,
    ) {}

    public function handle(): void
    {
        $student = Student::query()->with(['branch', 'user'])->findOrFail($this->studentId);
        $schoolYear = SchoolYear::query()->findOrFail($this->schoolYearId);

        $enrollment = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('school_year_id', $schoolYear->id)
            ->firstOrFail();

        $grades = Grade::query()
            ->where('enrollment_id', $enrollment->id)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();

        $periods = [
            Grade::PERIOD_Q1,
            Grade::PERIOD_Q2,
            Grade::PERIOD_Q3,
            Grade::PERIOD_Q4,
            Grade::PERIOD_FINAL,
        ];

        $rows = [];
        foreach ($grades->groupBy('subject_id') as $subjectGrades) {
            $first = $subjectGrades->first();
            if (! $first || ! $first->subject) {
                continue;
            }

            $subject = $first->subject;
            $row = [
                'name' => $subject->name,
                'code' => $subject->code,
            ];
            foreach ($periods as $p) {
                $g = $subjectGrades->firstWhere('period', $p);
                $row[$p] = $g !== null && $g->score !== null
                    ? number_format((float) $g->score, 2)
                    : '—';
            }
            $rows[] = $row;
        }

        $pdf = Pdf::loadView('pdf.report-card', [
            'student' => $student,
            'schoolYear' => $schoolYear,
            'enrollment' => $enrollment,
            'rows' => $rows,
            'periods' => $periods,
            'periodLabels' => Grade::periodOptions(),
        ]);

        $pdfBinary = $pdf->output();

        $media = $student->addMediaFromString($pdfBinary)
            ->usingFileName('report-card-'.$schoolYear->id.'-'.now()->format('Y-m-d_His').'.pdf')
            ->usingName('Report card — '.$schoolYear->name)
            ->withCustomProperties([
                'school_year_id' => $schoolYear->id,
            ])
            ->toMediaCollection('report_cards');

        if ($student->user) {
            $student->user->notify(new ReportCardReadyNotification(
                schoolYearId: $schoolYear->id,
                mediaId: $media->id,
            ));
        }
    }
}
