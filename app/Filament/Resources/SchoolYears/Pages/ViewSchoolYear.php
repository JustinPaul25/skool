<?php

namespace App\Filament\Resources\SchoolYears\Pages;

use App\Filament\Resources\SchoolYears\SchoolYearResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSchoolYear extends ViewRecord
{
    protected static string $resource = SchoolYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
