<?php

namespace App\Filament\Resources\Grades\Schemas;

use App\Models\Enrollment;
use App\Models\Grade;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GradeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grade')
                    ->schema([
                        TextEntry::make('enrollment')
                            ->label('Enrollment')
                            ->formatStateUsing(fn (?Enrollment $state): string => $state?->filamentLabel() ?? '—')
                            ->placeholder('—'),

                        TextEntry::make('subject.name')
                            ->label('Subject')
                            ->placeholder('—'),

                        TextEntry::make('period')
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
                            }),

                        TextEntry::make('score')
                            ->label('Score')
                            ->numeric(decimalPlaces: 2)
                            ->placeholder('—'),

                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('grader.name')
                            ->label('Graded by')
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }
}
