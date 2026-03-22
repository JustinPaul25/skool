<?php

namespace App\Filament\Resources\PaymentUtilities\Pages;

use App\Filament\Resources\PaymentUtilities\PaymentUtilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentUtilities extends ListRecords
{
    protected static string $resource = PaymentUtilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
