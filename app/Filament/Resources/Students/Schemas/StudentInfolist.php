<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Photo')
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('photo')
                            ->label('Student photo')
                            ->collection('photo')
                            ->height(200)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Identification')
                    ->schema([
                        TextEntry::make('student_id')
                            ->label('Student ID')
                            ->copyable()
                            ->weight('bold'),

                        TextEntry::make('branch.name')
                            ->label('Branch')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('user.name')
                            ->label('Linked user')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                Section::make('Personal')
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Full name'),

                        TextEntry::make('birth_date')
                            ->date(),

                        TextEntry::make('gender')
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make('Contact')
                    ->schema([
                        TextEntry::make('address')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->placeholder('—'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Guardian')
                    ->schema([
                        TextEntry::make('guardian_name'),
                        TextEntry::make('guardian_phone')
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('guardian_relationship')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                Section::make('System')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->since(),

                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Student $record): bool => $record->trashed()),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }
}
