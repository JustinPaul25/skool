<?php

namespace App\Filament\Resources\SchoolYears\Tables;

use App\Services\SchoolYearService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchoolYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('School Year'),

                TextColumn::make('start_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->label('Start Date'),

                TextColumn::make('end_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->label('End Date'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->label('Active'),

                TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->badge()
                    ->color('primary')
                    ->label('Enrollments'),

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
                Action::make('set_active')
                    ->label('Set Active')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('Set Active School Year')
                    ->modalDescription('This will deactivate the current active school year and set this as the active year.')
                    ->action(function ($record) {
                        $service = app(SchoolYearService::class);
                        $service->setActive($record);

                        Notification::make()
                            ->success()
                            ->title('School year activated')
                            ->body("'{$record->name}' is now the active school year.")
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
