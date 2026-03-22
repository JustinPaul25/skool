<?php

use App\Models\Branch;
use App\Models\GradeLevel;
use App\Models\PaymentUtility;
use App\Models\Requirement;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Database\Seeders\TestingSchoolDataSeeder;

it('seeds testing school domain records', function () {
    $this->seed(TestingSchoolDataSeeder::class);

    expect(Branch::query()->where('code', 'TEST-MAIN')->exists())->toBeTrue();
    expect(SchoolYear::query()->where('name', '2025-2026')->exists())->toBeTrue();
    expect(GradeLevel::query()->where('name', 'Grade 7')->exists())->toBeTrue();
    expect(GradeLevel::query()->where('name', 'Grade 8')->exists())->toBeTrue();
    expect(Subject::query()->where('code', 'TEST-MATH-G7')->exists())->toBeTrue();
    expect(Section::query()->where('name', 'Section A')->exists())->toBeTrue();
    expect(PaymentUtility::query()->where('name', 'Annual Tuition (G7)')->exists())->toBeTrue();
    expect(Requirement::query()->where('name', 'Birth Certificate')->exists())->toBeTrue();
    expect(Student::query()->whereIn('student_id', ['TEST-STU-00001', 'TEST-STU-00002'])->count())->toBe(2);
});
