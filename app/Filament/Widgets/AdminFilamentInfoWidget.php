<?php

namespace App\Filament\Widgets;

use Filament\Widgets\FilamentInfoWidget as BaseFilamentInfoWidget;

class AdminFilamentInfoWidget extends BaseFilamentInfoWidget
{
    public static function canView(): bool
    {
        if (auth()->user()?->hasRole('branch_manager')) {
            return false;
        }

        return parent::canView();
    }
}
