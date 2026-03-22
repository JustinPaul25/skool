<?php

namespace App\Filament\Resources\Requirements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RequirementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Requirement')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Name')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->label('Description')
                            ->placeholder('Optional details for staff and applicants'),

                        Toggle::make('is_required')
                            ->label('Required')
                            ->default(true)
                            ->helperText('When enabled, this document is mandatory for applicable enrollments'),

                        Select::make('grade_level_id')
                            ->relationship('gradeLevel', 'name', fn ($query) => $query->orderBy('order'))
                            ->searchable()
                            ->preload()
                            ->label('Applies to grade level')
                            ->helperText('Leave empty to apply this requirement to all grade levels')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
