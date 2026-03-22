<?php

namespace App\Filament\Resources\GradeLevels\Pages;

use App\Filament\Resources\GradeLevels\GradeLevelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGradeLevel extends ViewRecord
{
    protected static string $resource = GradeLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
