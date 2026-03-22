<?php

namespace App\Filament\Resources\GradeLevels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GradeLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Grade Level Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Grade Level Name')
                            ->placeholder('e.g., Grade 1, Grade 7, Year 12')
                            ->helperText('Enter the name or level designation'),

                        TextInput::make('order')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->label('Display Order')
                            ->helperText('Lower numbers appear first in lists'),

                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Branch')
                            ->helperText('Leave empty for system-wide grade level')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
