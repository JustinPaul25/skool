<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'student_id',
        'school_year_id',
        'grade_level_id',
        'section_id',
        'branch_id',
        'status',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['student_id', 'school_year_id', 'grade_level_id', 'status'])
            ->logOnlyDirty();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Human-readable label for selects and tables (student + school year + grade level).
     */
    public function filamentLabel(): string
    {
        $this->loadMissing(['student', 'schoolYear', 'gradeLevel']);

        return sprintf(
            '%s — %s — %s',
            $this->student?->full_name ?? '?',
            $this->schoolYear?->name ?? '?',
            $this->gradeLevel?->name ?? '?',
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function studentRequirements(): HasMany
    {
        return $this->hasMany(StudentRequirement::class);
    }
}
