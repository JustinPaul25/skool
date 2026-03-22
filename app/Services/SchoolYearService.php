<?php

namespace App\Services;

use App\Models\SchoolYear;
use Illuminate\Support\Facades\DB;

class SchoolYearService
{
    /**
     * Set a school year as active and deactivate all others.
     */
    public function setActive(SchoolYear $schoolYear): void
    {
        DB::transaction(function () use ($schoolYear) {
            // Deactivate all school years
            SchoolYear::where('is_active', true)->update(['is_active' => false]);

            // Activate the specified school year
            $schoolYear->update(['is_active' => true]);
        });
    }

    /**
     * Get the currently active school year.
     */
    public function getActive(): ?SchoolYear
    {
        return SchoolYear::where('is_active', true)->first();
    }

    /**
     * Check if a school year can be deleted.
     */
    public function canDelete(SchoolYear $schoolYear): bool
    {
        // Cannot delete if it's active
        if ($schoolYear->is_active) {
            return false;
        }

        // Cannot delete if it has enrollments
        if ($schoolYear->enrollments()->exists()) {
            return false;
        }

        return true;
    }
}
