<?php

namespace App\Http\Controllers\Portal\Concerns;

use App\Models\Enrollment;
use App\Models\Requirement;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;

trait ResolvesPortalStudent
{
    protected function portalStudent(Request $request): Student
    {
        $student = $request->user()?->student;

        abort_if($student === null, 404, __('No student profile is linked to your account.'));

        return $student;
    }

    protected function activeSchoolYear(): ?SchoolYear
    {
        return SchoolYear::query()->where('is_active', true)->first();
    }

    protected function activeEnrollment(Student $student): ?Enrollment
    {
        $year = $this->activeSchoolYear();

        if ($year === null) {
            return null;
        }

        return $student->enrollments()
            ->where('school_year_id', $year->id)
            ->with(['gradeLevel', 'section', 'schoolYear', 'branch'])
            ->first();
    }

    protected function requirementAppliesToEnrollment(Requirement $requirement, Enrollment $enrollment): bool
    {
        if ($requirement->grade_level_id === null) {
            return true;
        }

        return (int) $requirement->grade_level_id === (int) $enrollment->grade_level_id;
    }
}
