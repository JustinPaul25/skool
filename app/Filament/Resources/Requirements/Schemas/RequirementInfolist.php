<?php

namespace App\Filament\Resources\Requirements\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RequirementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Requirement')
                    ->schema([
                        TextEntry::make('name')
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('description')
                            ->placeholder('No description')
                            ->columnSpanFull(),

                        IconEntry::make('is_required')
                            ->label('Required')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('danger')
                            ->falseColor('gray'),

                        TextEntry::make('gradeLevel.name')
                            ->label('Applies to grade level')
                            ->badge()
                            ->color('info')
                            ->placeholder('All grade levels'),
                    ])
                    ->columns(2),

                Section::make('Usage')
                    ->schema([
                        TextEntry::make('student_requirements_count')
                            ->label('Linked submissions')
                            ->badge()
                            ->color('primary')
                            ->getStateUsing(fn ($record) => $record->studentRequirements()->count()),
                    ])
                    ->columns(1)
                    ->collapsed(),

                Section::make('System')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->since(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
