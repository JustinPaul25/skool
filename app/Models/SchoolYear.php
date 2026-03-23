<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
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

    public function branchAccounts(): HasMany
    {
        return $this->hasMany(BranchAccount::class);
    }

    /**
     * School year used for enrollments, portal, dashboards, and reports.
     * Honors Settings → active school year override when set; otherwise the year marked active in the database.
     */
    public static function appCurrent(): ?self
    {
        $settings = SchoolSetting::query()->first();

        if ($settings?->active_school_year_id) {
            return self::query()->find($settings->active_school_year_id);
        }

        return self::query()->where('is_active', true)->first();
    }
}
