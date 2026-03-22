<?php

namespace App\Filament\Resources\EnrollmentApplications\Pages;

use App\Filament\Resources\EnrollmentApplications\EnrollmentApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnrollmentApplications extends ListRecords
{
    protected static string $resource = EnrollmentApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
