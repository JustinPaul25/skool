<?php

namespace App\Filament\Resources\Sections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Section Name'),

                TextColumn::make('gradeLevel.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->label('Grade Level'),

                TextColumn::make('subject.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->label('Subject')
                    ->placeholder('—'),

                TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->label('Branch'),

                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable()
                    ->label('Capacity')
                    ->placeholder('Unlimited'),

                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->badge()
                    ->color('primary')
                    ->label('Students')
                    ->description(fn ($record) => $record->capacity ? ($record->enrollments_count.'/'.$record->capacity) : null),

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
                SelectFilter::make('subject')
                    ->relationship('subject', 'name')
                    ->label('Subject'),
                SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Branch'),
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
            ->defaultSort('name', 'asc');
    }
}
