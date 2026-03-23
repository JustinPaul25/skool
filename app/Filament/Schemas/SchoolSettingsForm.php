<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Branding'))
                    ->schema([
                        TextInput::make('school_name')
                            ->label(__('School name'))
                            ->maxLength(255),

                        SpatieMediaLibraryFileUpload::make('logo')
                            ->label(__('School logo'))
                            ->collection('logo')
                            ->image()
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('Contact'))
                    ->schema([
                        Textarea::make('address')
                            ->label(__('Address'))
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Section::make(__('Academic year'))
                    ->schema([
                        Select::make('active_school_year_id')
                            ->label(__('Active school year override'))
                            ->relationship('activeSchoolYear', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('When set, this year is used for enrollment, portal, dashboards, and bulk reports instead of the year marked active on the School Years resource.'))
                            ->nullable(),
                    ])
                    ->columns(1),

                Section::make(__('Email'))
                    ->schema([
                        Textarea::make('email_footer_text')
                            ->label(__('Email footer text'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
