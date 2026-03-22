<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subject Information')
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Subject Code')
                            ->placeholder('e.g., MATH101, ENG102')
                            ->helperText('Unique identifier for this subject'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Subject Name')
                            ->placeholder('e.g., Mathematics, English Literature'),

                        Select::make('grade_level_id')
                            ->relationship('gradeLevel', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Grade Level'),

                        TextInput::make('units')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->label('Units/Credits')
                            ->helperText('Credit hours or units for this subject')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
