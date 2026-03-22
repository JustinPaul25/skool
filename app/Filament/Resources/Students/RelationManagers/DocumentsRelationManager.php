<?php

namespace App\Filament\Resources\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'studentRequirements';

    protected static ?string $title = 'Documents & requirements';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requirement.name')
                    ->label('Requirement')
                    ->searchable(),

                TextColumn::make('enrollment.schoolYear.name')
                    ->label('School year')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'submitted' => 'info',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->placeholder('—'),

                TextColumn::make('verifier.name')
                    ->label('Verified by')
                    ->placeholder('—'),
            ])
            ->defaultSort('requirement.name');
    }
}
