<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Section name'),

                        TextEntry::make('gradeLevel.name')
                            ->label('Grade level')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('subject.name')
                            ->label('Subject')
                            ->badge()
                            ->color('warning')
                            ->placeholder('—'),

                        TextEntry::make('branch.name')
                            ->label('Branch')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('capacity')
                            ->numeric()
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('System information')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
