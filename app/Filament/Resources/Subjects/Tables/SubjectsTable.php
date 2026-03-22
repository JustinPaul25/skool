<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->label('Code'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Subject Name'),

                TextColumn::make('gradeLevel.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->label('Grade Level'),

                TextColumn::make('units')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->label('Units')
                    ->placeholder('N/A')
                    ->suffix(' units'),

                TextColumn::make('grades_count')
                    ->counts('grades')
                    ->badge()
                    ->color('primary')
                    ->label('Grades Recorded'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('grade_level')
                    ->relationship('gradeLevel', 'name')
                    ->label('Grade Level'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc');
    }
}
