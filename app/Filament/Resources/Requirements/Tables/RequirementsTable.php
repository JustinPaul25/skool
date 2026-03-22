<?php

namespace App\Filament\Resources\Requirements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RequirementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->label('Requirement'),

                TextColumn::make('is_required')
                    ->label('Required')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Required' : 'Optional')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'gray'),

                TextColumn::make('gradeLevel.name')
                    ->label('Grade level')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('All grades'),

                TextColumn::make('student_requirements_count')
                    ->counts('studentRequirements')
                    ->label('Submissions')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

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
                TernaryFilter::make('is_required')
                    ->label('Required')
                    ->placeholder('All')
                    ->trueLabel('Required only')
                    ->falseLabel('Optional only'),
                SelectFilter::make('grade_level_id')
                    ->relationship('gradeLevel', 'name', fn ($query) => $query->orderBy('order'))
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
