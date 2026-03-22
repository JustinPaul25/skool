<?php

namespace App\Filament\Resources\PaymentUtilities\Schemas;

use App\Models\PaymentUtility;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentUtilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fee configuration')
                    ->description('Define a fee line item for the selected school year. Branch and grade level are optional scoping rules.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₱')
                            ->helperText('Use positive amounts; use type “Discount” for reductions.'),

                        Select::make('type')
                            ->label('Type')
                            ->options(PaymentUtility::typeOptions())
                            ->required()
                            ->native(false),

                        Select::make('school_year_id')
                            ->label('School year')
                            ->relationship('schoolYear', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('grade_level_id')
                            ->label('Grade level')
                            ->relationship('gradeLevel', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Leave empty to apply to all grade levels.'),

                        Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Leave empty for all branches.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive fees are hidden from billing helpers but kept for history.'),
                    ])
                    ->columns(2),
            ]);
    }
}
