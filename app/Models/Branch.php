<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'is_active',
        'commission_rate',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function gradeLevels(): HasMany
    {
        return $this->hasMany(GradeLevel::class);
    }

    public function enrollmentApplications(): HasMany
    {
        return $this->hasMany(EnrollmentApplication::class);
    }

    public function paymentUtilities(): HasMany
    {
        return $this->hasMany(PaymentUtility::class);
    }

    public function branchAccounts(): HasMany
    {
        return $this->hasMany(BranchAccount::class);
    }
}
