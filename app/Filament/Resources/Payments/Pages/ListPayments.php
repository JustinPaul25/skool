<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Exports\PaymentsExport;
use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $filename = 'payments-'.now()->format('Y-m-d_His').'.xlsx';

                    return Excel::download(new PaymentsExport, $filename);
                }),
            CreateAction::make(),
        ];
    }
}
