<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Branch Name'),

                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Branch Code')
                            ->helperText('Unique identifier for this branch'),

                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull()
                            ->label('Address'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255)
                            ->label('Phone Number'),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->label('Email Address'),
                    ])
                    ->columns(2),

                Section::make('Branch Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Inactive branches will not appear in selection lists'),
                    ])
                    ->columns(1),
            ]);
    }
}
