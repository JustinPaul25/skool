<?php

namespace App\Filament\Resources\PaymentUtilities\Schemas;

use App\Models\PaymentUtility;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentUtilityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name'),

                TextEntry::make('amount')
                    ->label('Amount')
                    ->money('PHP'),

                TextEntry::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PaymentUtility::typeLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        PaymentUtility::TYPE_TUITION => 'primary',
                        PaymentUtility::TYPE_MISCELLANEOUS => 'info',
                        PaymentUtility::TYPE_DISCOUNT => 'warning',
                        PaymentUtility::TYPE_PENALTY => 'danger',
                        default => 'gray',
                    }),

                TextEntry::make('gradeLevel.name')
                    ->label('Grade level')
                    ->placeholder('All grade levels'),

                TextEntry::make('branch.name')
                    ->label('Branch')
                    ->placeholder('All branches'),

                TextEntry::make('schoolYear.name')
                    ->label('School year'),

                TextEntry::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('—'),
            ]);
    }
}
