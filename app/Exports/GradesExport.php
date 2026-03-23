<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GradesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected ?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    /**
     * @return Builder<Grade>
     */
    public function query(): Builder
    {
        $query = Grade::query()
            ->with([
                'enrollment.student',
                'enrollment.schoolYear',
                'enrollment.branch',
                'subject',
            ]);

        if ($this->user && $this->user->hasRole('branch_manager') && $this->user->branch_id) {
            $query->whereHas('enrollment', fn (Builder $q) => $q->where('branch_id', $this->user->branch_id));
        }

        return $query
            ->orderBy('enrollment_id')
            ->orderBy('subject_id')
            ->orderBy('period');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'student_id',
            'student_name',
            'school_year',
            'branch',
            'subject_code',
            'subject_name',
            'period',
            'score',
            'remarks',
        ];
    }

    /**
     * @return array<int, string|null>
     */
    public function map(mixed $grade): array
    {
        /** @var Grade $grade */
        $student = $grade->enrollment?->student;

        return [
            $student?->student_id,
            $student?->full_name,
            $grade->enrollment?->schoolYear?->name,
            $grade->enrollment?->branch?->name,
            $grade->subject?->code,
            $grade->subject?->name,
            $grade->period,
            $grade->score !== null ? (string) $grade->score : null,
            $grade->remarks,
        ];
    }
}
