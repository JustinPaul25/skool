<?php

namespace App\Filament\Resources\PaymentUtilities\Pages;

use App\Filament\Resources\PaymentUtilities\PaymentUtilityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentUtility extends ViewRecord
{
    protected static string $resource = PaymentUtilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
