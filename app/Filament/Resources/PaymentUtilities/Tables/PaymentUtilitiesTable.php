<?php

namespace App\Filament\Resources\PaymentUtilities\Tables;

use App\Models\PaymentUtility;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentUtilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PaymentUtility::typeLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        PaymentUtility::TYPE_TUITION => 'primary',
                        PaymentUtility::TYPE_MISCELLANEOUS => 'info',
                        PaymentUtility::TYPE_DISCOUNT => 'warning',
                        PaymentUtility::TYPE_PENALTY => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gradeLevel.name')
                    ->label('Grade level')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('schoolYear.name')
                    ->label('School year')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),

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
                SelectFilter::make('type')
                    ->options(PaymentUtility::typeOptions())
                    ->label('Type'),

                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All records')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('school_year_id')
                    ->relationship('schoolYear', 'name')
                    ->label('School year')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('grade_level_id')
                    ->relationship('gradeLevel', 'name')
                    ->label('Grade level')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('name');
    }
}
