<?php

namespace App\Filament\Resources\Requirements\Pages;

use App\Filament\Resources\Requirements\RequirementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRequirement extends ViewRecord
{
    protected static string $resource = RequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
