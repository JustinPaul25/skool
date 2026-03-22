<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Section Name')
                            ->placeholder('e.g., Section A, Diamond, Einstein'),

                        Select::make('grade_level_id')
                            ->relationship('gradeLevel', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('subject_id', null))
                            ->label('Grade Level'),

                        Select::make('subject_id')
                            ->label('Subject')
                            ->relationship(
                                name: 'subject',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get): Builder {
                                    if (blank($get('grade_level_id'))) {
                                        return $query->whereRaw('0 = 1');
                                    }

                                    return $query->where('grade_level_id', $get('grade_level_id'));
                                },
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get): bool => blank($get('grade_level_id')))
                            ->helperText('Subjects are limited to the selected grade level.'),

                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Branch'),

                        TextInput::make('capacity')
                            ->numeric()
                            ->minValue(1)
                            ->label('Capacity')
                            ->helperText('Maximum number of students')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
