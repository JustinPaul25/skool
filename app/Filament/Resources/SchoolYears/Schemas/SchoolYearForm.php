<?php

namespace App\Filament\Resources\SchoolYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('School Year Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('School Year Name')
                            ->placeholder('e.g., 2025-2026')
                            ->helperText('Enter the school year in format: YYYY-YYYY'),

                        DatePicker::make('start_date')
                            ->required()
                            ->native(false)
                            ->label('Start Date')
                            ->before('end_date'),

                        DatePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->label('End Date')
                            ->after('start_date'),

                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(false)
                            ->helperText('Only one school year can be active at a time. Activating this will deactivate all others.')
                            ->disabled(fn ($record) => $record?->is_active),
                    ])
                    ->columns(2),
            ]);
    }
}
