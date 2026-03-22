<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentReceiptService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable()
                    ->summarize(Sum::make()->label('Subtotal')),

                TextColumn::make('type')
                    ->badge(),

                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('receiver.name')
                    ->label('Received by')
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('print_or')
                    ->label('Print OR')
                    ->icon('heroicon-o-printer')
                    ->authorize('view')
                    ->action(fn (Payment $record) => app(PaymentReceiptService::class)->download($record)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Record payment')
                    ->url(function (): string {
                        return PaymentResource::getUrl('create', [
                            'account_id' => $this->getOwnerRecord()->account?->id,
                        ]);
                    })
                    ->visible(fn (): bool => $this->getOwnerRecord()->account !== null),
            ])
            ->defaultSort('paid_at', 'desc');
    }
}
