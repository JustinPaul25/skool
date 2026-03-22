<?php

namespace App\Filament\Resources\Grades\Tables;

use App\Models\Enrollment;
use App\Models\Grade;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment')
                    ->label('Enrollment')
                    ->formatStateUsing(fn (?Enrollment $state): string => $state?->filamentLabel() ?? '—')
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('enrollment.student', function ($q) use ($search): void {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('student_id', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('enrollment.schoolYear.name')
                    ->label('School year')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subject.code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('period')
                    ->label('Period')
                    ->formatStateUsing(fn (?string $state): string => $state ? Grade::periodLabel($state) : '—')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Grade::PERIOD_Q1 => 'primary',
                        Grade::PERIOD_Q2 => 'info',
                        Grade::PERIOD_Q3 => 'warning',
                        Grade::PERIOD_Q4 => 'success',
                        Grade::PERIOD_FINAL => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('score')
                    ->label('Score')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(80)
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('grader.name')
                    ->label('Graded by')
                    ->placeholder('—')
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('period')
                    ->label('Period')
                    ->options(Grade::periodOptions()),

                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('school_year_id')
                    ->label('School year')
                    ->relationship('enrollment.schoolYear', 'name')
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
            ]);
    }
}
