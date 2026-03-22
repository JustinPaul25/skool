<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requirement extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_required',
        'grade_level_id',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function studentRequirements(): HasMany
    {
        return $this->hasMany(StudentRequirement::class);
    }
}
