<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\GradeLevel;
use App\Models\PaymentUtility;
use App\Models\Requirement;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

/**
 * Sample records for manual QA: branch, school year, grade levels, subjects, sections,
 * payment utilities, requirements, and students. Safe to run repeatedly (uses firstOrCreate).
 */
class TestingSchoolDataSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::query()->firstOrCreate(
            ['code' => 'TEST-MAIN'],
            [
                'name' => 'Main Campus (Test)',
                'address' => '123 Education Street',
                'phone' => '+1-555-0100',
                'email' => 'main@test-school.local',
                'is_active' => true,
                'commission_rate' => '5.00',
            ],
        );

        $schoolYear = SchoolYear::query()->firstOrCreate(
            ['name' => '2025-2026'],
            [
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'is_active' => true,
            ],
        );

        $grade7 = GradeLevel::query()->firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Grade 7'],
            ['order' => 7],
        );

        $grade8 = GradeLevel::query()->firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Grade 8'],
            ['order' => 8],
        );

        $math = Subject::query()->firstOrCreate(
            ['code' => 'TEST-MATH-G7'],
            [
                'name' => 'Mathematics',
                'grade_level_id' => $grade7->id,
                'units' => '4.00',
            ],
        );

        $english = Subject::query()->firstOrCreate(
            ['code' => 'TEST-ENG-G7'],
            [
                'name' => 'English',
                'grade_level_id' => $grade7->id,
                'units' => '4.00',
            ],
        );

        Subject::query()->firstOrCreate(
            ['code' => 'TEST-SCI-G8'],
            [
                'name' => 'Science',
                'grade_level_id' => $grade8->id,
                'units' => '4.00',
            ],
        );

        Section::query()->firstOrCreate(
            [
                'branch_id' => $branch->id,
                'grade_level_id' => $grade7->id,
                'name' => 'Section A',
            ],
            [
                'subject_id' => $math->id,
                'capacity' => 35,
            ],
        );

        Section::query()->firstOrCreate(
            [
                'branch_id' => $branch->id,
                'grade_level_id' => $grade7->id,
                'name' => 'Section B',
            ],
            [
                'subject_id' => $english->id,
                'capacity' => 35,
            ],
        );

        PaymentUtility::query()->firstOrCreate(
            [
                'branch_id' => $branch->id,
                'school_year_id' => $schoolYear->id,
                'name' => 'Annual Tuition (G7)',
                'type' => PaymentUtility::TYPE_TUITION,
            ],
            [
                'amount' => '25000.00',
                'grade_level_id' => $grade7->id,
                'is_active' => true,
            ],
        );

        PaymentUtility::query()->firstOrCreate(
            [
                'branch_id' => $branch->id,
                'school_year_id' => $schoolYear->id,
                'name' => 'Lab Fee (G7)',
                'type' => PaymentUtility::TYPE_MISCELLANEOUS,
            ],
            [
                'amount' => '1500.00',
                'grade_level_id' => $grade7->id,
                'is_active' => true,
            ],
        );

        PaymentUtility::query()->firstOrCreate(
            [
                'branch_id' => $branch->id,
                'school_year_id' => $schoolYear->id,
                'name' => 'Early Bird Discount (G8)',
                'type' => PaymentUtility::TYPE_DISCOUNT,
            ],
            [
                'amount' => '2000.00',
                'grade_level_id' => $grade8->id,
                'is_active' => true,
            ],
        );

        Requirement::query()->firstOrCreate(
            ['grade_level_id' => $grade7->id, 'name' => 'Birth Certificate'],
            [
                'description' => 'PSA or local civil registry copy.',
                'is_required' => true,
            ],
        );

        Requirement::query()->firstOrCreate(
            ['grade_level_id' => $grade7->id, 'name' => 'Previous Report Card'],
            [
                'description' => 'Most recent completed school year.',
                'is_required' => true,
            ],
        );

        Student::query()->firstOrCreate(
            ['student_id' => 'TEST-STU-00001'],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Alex',
                'last_name' => 'Rivera',
                'middle_name' => 'M.',
                'birth_date' => '2013-04-15',
                'gender' => 'male',
                'address' => '456 Student Lane',
                'phone' => '+1-555-0201',
                'email' => 'alex.rivera.test@example.local',
                'guardian_name' => 'Maria Rivera',
                'guardian_phone' => '+1-555-0202',
                'guardian_relationship' => 'Mother',
            ],
        );

        Student::query()->firstOrCreate(
            ['student_id' => 'TEST-STU-00002'],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Jamie',
                'last_name' => 'Chen',
                'middle_name' => null,
                'birth_date' => '2013-08-22',
                'gender' => 'female',
                'address' => '789 Learner Ave',
                'phone' => null,
                'email' => 'jamie.chen.test@example.local',
                'guardian_name' => 'Wei Chen',
                'guardian_phone' => '+1-555-0203',
                'guardian_relationship' => 'Father',
            ],
        );
    }
}
