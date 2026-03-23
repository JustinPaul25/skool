<?php

namespace App\Filament\Concerns;

trait InteractsWithDashboardBranchScope
{
    protected function scopedBranchId(): ?int
    {
        $user = auth()->user();

        if ($user?->hasRole('branch_manager') && $user->branch_id) {
            return (int) $user->branch_id;
        }

        return null;
    }
}
