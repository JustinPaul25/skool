<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('students', 'email')],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:50'],
            'guardian_relationship' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['required', 'exists:branches,id'],
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        if ($email === '' || $email === null) {
            $this->merge(['email' => null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $branchId = (int) $this->input('branch_id');
            $gradeLevelId = (int) $this->input('grade_level_id');

            $branch = \App\Models\Branch::query()->find($branchId);
            if ($branch && ! $branch->is_active) {
                $validator->errors()->add('branch_id', 'The selected branch is not available.');
            }

            $grade = \App\Models\GradeLevel::query()->find($gradeLevelId);
            if ($grade && $grade->branch_id !== null && (int) $grade->branch_id !== $branchId) {
                $validator->errors()->add('grade_level_id', 'The selected grade level is not available for this branch.');
            }
        });
    }
}
