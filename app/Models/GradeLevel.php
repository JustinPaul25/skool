<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeLevel extends Model
{
    protected $fillable = [
        'name',
        'order',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrollmentApplications(): HasMany
    {
        return $this->hasMany(EnrollmentApplication::class);
    }

    public function paymentUtilities(): HasMany
    {
        return $this->hasMany(PaymentUtility::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }
}
