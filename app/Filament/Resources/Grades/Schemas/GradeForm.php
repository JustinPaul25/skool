<?php

namespace App\Filament\Resources\Grades\Schemas;

use App\Models\Enrollment;
use App\Models\Grade;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grade')
                    ->schema([
                        Select::make('enrollment_id')
                            ->label('Enrollment')
                            ->relationship(
                                name: 'enrollment',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query
                                    ->with(['student', 'schoolYear', 'gradeLevel'])
                                    ->orderBy('id'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Enrollment $record): string => $record->filamentLabel())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('subject_id')
                            ->label('Subject')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('period')
                            ->label('Period')
                            ->options(Grade::periodOptions())
                            ->default(Grade::PERIOD_Q1)
                            ->required(),

                        TextInput::make('score')
                            ->label('Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required(),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
