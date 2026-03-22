<?php

namespace App\Filament\Resources\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Enrollments';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolYear.name')
                    ->label('School year')
                    ->sortable(),

                TextColumn::make('gradeLevel.name')
                    ->label('Grade level')
                    ->sortable(),

                TextColumn::make('section.name')
                    ->label('Section')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'enrolled' => 'success',
                        'pending' => 'warning',
                        'dropped' => 'danger',
                        'graduated' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('enrolled_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('enrolled_at', 'desc');
    }
}
