<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student ID & Branch')
                    ->schema([
                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated when creating via the wizard'),

                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Linked user account')
                            ->nullable(),

                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Branch')
                            ->default(fn () => auth()->user()?->branch_id)
                            ->disabled(fn () => auth()->user()?->hasRole('branch_manager') ?? false),
                    ])
                    ->columns(2),

                Section::make('Personal')
                    ->schema(array_merge(
                        self::personalFields(),
                        [
                            SpatieMediaLibraryFileUpload::make('photo')
                                ->label('Photo')
                                ->collection('photo')
                                ->image()
                                ->imageEditor()
                                ->maxSize(5120)
                                ->columnSpanFull(),
                        ]
                    ))
                    ->columns(2),

                Section::make('Contact')
                    ->schema(self::contactFields())
                    ->columns(2),

                Section::make('Guardian')
                    ->schema(self::guardianFields())
                    ->columns(2),
            ]);
    }

    /**
     * @return array<int, Component>
     */
    public static function personalFields(): array
    {
        return [
            TextInput::make('first_name')
                ->required()
                ->maxLength(255),

            TextInput::make('last_name')
                ->required()
                ->maxLength(255),

            TextInput::make('middle_name')
                ->maxLength(255),

            DatePicker::make('birth_date')
                ->required()
                ->native(false),

            Select::make('gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                ])
                ->default('male')
                ->required(),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function contactFields(): array
    {
        return [
            Textarea::make('address')
                ->rows(3)
                ->columnSpanFull(),

            TextInput::make('phone')
                ->tel()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->maxLength(255),
        ];
    }

    /**
     * @return array<int, Component>
     */
    public static function guardianFields(): array
    {
        return [
            TextInput::make('guardian_name')
                ->required()
                ->maxLength(255),

            TextInput::make('guardian_phone')
                ->tel()
                ->required()
                ->maxLength(255),

            TextInput::make('guardian_relationship')
                ->maxLength(255),
        ];
    }
}
