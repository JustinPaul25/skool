<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\AccountWidget as BaseAccountWidget;

class AdminAccountWidget extends BaseAccountWidget
{
    public static function canView(): bool
    {
        if (auth()->user()?->hasRole('branch_manager')) {
            return false;
        }

        return Filament::auth()->check();
    }
}
