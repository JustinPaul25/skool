<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_id')
                    ->searchable()
                    ->sortable()
                    ->label('Student ID')
                    ->copyable(),

                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderBy('last_name', $direction)->orderBy('first_name', $direction);
                    })
                    ->weight('medium'),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('current_grade')
                    ->label('Grade level')
                    ->getStateUsing(function ($record) {
                        return $record->enrollments()
                            ->with('gradeLevel')
                            ->latest('enrolled_at')
                            ->first()
                            ?->gradeLevel
                            ?->name ?? '—';
                    }),

                TextColumn::make('enrollment_status')
                    ->label('Status')
                    ->badge()
                    ->placeholder('—')
                    ->color(fn (?string $state): string => match ($state) {
                        'enrolled' => 'success',
                        'pending' => 'warning',
                        'dropped' => 'danger',
                        'graduated' => 'primary',
                        default => 'gray',
                    })
                    ->getStateUsing(function ($record) {
                        return $record->enrollments()->latest('enrolled_at')->value('status');
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
