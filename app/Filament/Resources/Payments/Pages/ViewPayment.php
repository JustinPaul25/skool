<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Services\PaymentReceiptService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_or')
                ->label('Print OR')
                ->icon('heroicon-o-printer')
                ->authorize('view')
                ->action(fn () => app(PaymentReceiptService::class)->download($this->getRecord())),
            EditAction::make(),
        ];
    }
}
