<?php

namespace App\Filament\Resources\PaymentUtilities\Pages;

use App\Filament\Resources\PaymentUtilities\PaymentUtilityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentUtility extends EditRecord
{
    protected static string $resource = PaymentUtilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
