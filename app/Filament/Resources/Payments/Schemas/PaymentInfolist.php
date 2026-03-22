<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reference_no')
                    ->label('Reference no. (OR)')
                    ->copyable(),

                TextEntry::make('account.student.full_name')
                    ->label('Student'),

                TextEntry::make('account.student.student_id')
                    ->label('Student ID')
                    ->placeholder('—'),

                TextEntry::make('enrollment.schoolYear.name')
                    ->label('School year (enrollment)')
                    ->placeholder('—'),

                TextEntry::make('amount')
                    ->money('PHP'),

                TextEntry::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->color(fn (string $state): string => match ($state) {
                        'tuition' => 'primary',
                        'miscellaneous' => 'info',
                        'discount' => 'warning',
                        'penalty' => 'danger',
                        default => 'gray',
                    }),

                TextEntry::make('receiver.name')
                    ->label('Received by')
                    ->placeholder('—'),

                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('notes')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('—'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('—'),
            ]);
    }
}
