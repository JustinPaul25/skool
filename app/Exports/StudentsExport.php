<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected ?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    /**
     * @return Builder<Student>
     */
    public function query(): Builder
    {
        $query = Student::query()->with(['branch']);

        if ($this->user && $this->user->hasRole('branch_manager') && $this->user->branch_id) {
            $query->where('branch_id', $this->user->branch_id);
        }

        return $query->orderBy('last_name')->orderBy('first_name');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'student_id',
            'first_name',
            'middle_name',
            'last_name',
            'birth_date',
            'gender',
            'email',
            'phone',
            'branch',
            'guardian_name',
            'guardian_phone',
            'guardian_relationship',
        ];
    }

    /**
     * @return array<int, string|null>
     */
    public function map(mixed $student): array
    {
        /** @var Student $student */
        return [
            $student->student_id,
            $student->first_name,
            $student->middle_name,
            $student->last_name,
            $student->birth_date?->format('Y-m-d'),
            $student->gender,
            $student->email,
            $student->phone,
            $student->branch?->name,
            $student->guardian_name,
            $student->guardian_phone,
            $student->guardian_relationship,
        ];
    }
}
