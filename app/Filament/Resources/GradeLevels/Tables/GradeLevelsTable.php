<?php

namespace App\Filament\Resources\GradeLevels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GradeLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Grade Level'),

                TextColumn::make('order')
                    ->numeric()
                    ->sortable()
                    ->label('Order')
                    ->badge()
                    ->color('info'),

                TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable()
                    ->label('Branch')
                    ->placeholder('System-wide')
                    ->badge()
                    ->color('success'),

                TextColumn::make('sections_count')
                    ->counts('sections')
                    ->badge()
                    ->color('primary')
                    ->label('Sections'),

                TextColumn::make('subjects_count')
                    ->counts('subjects')
                    ->badge()
                    ->color('warning')
                    ->label('Subjects'),

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
            ->defaultSort('order', 'asc');
    }
}
