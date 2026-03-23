<?php

namespace App\Filament\Concerns;

trait HidesNavigationForBranchManagers
{
    public static function shouldRegisterNavigation(): bool
    {
        if (auth()->user()?->hasRole('branch_manager')) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }
}
