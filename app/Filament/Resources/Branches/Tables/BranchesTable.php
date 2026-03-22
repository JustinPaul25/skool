<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Branch Name'),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->label('Code'),

                TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->label('Phone'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label('Active'),

                TextColumn::make('students_count')
                    ->counts('students')
                    ->badge()
                    ->color('primary')
                    ->label('Students'),

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
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => ($record->is_active ? 'Deactivate' : 'Activate').' Branch')
                    ->modalDescription(fn ($record) => 'Are you sure you want to '.($record->is_active ? 'deactivate' : 'activate').' this branch?')
                    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                    ->successNotificationTitle(fn ($record) => 'Branch '.($record->is_active ? 'activated' : 'deactivated').' successfully'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
