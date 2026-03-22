<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Branch Name')
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('code')
                            ->label('Branch Code')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('address')
                            ->placeholder('No address provided')
                            ->columnSpanFull()
                            ->icon('heroicon-o-map-pin'),

                        TextEntry::make('phone')
                            ->placeholder('No phone provided')
                            ->icon('heroicon-o-phone'),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->placeholder('No email provided')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                    ])
                    ->columns(2),

                Section::make('Branch Settings')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])
                    ->columns(1),

                Section::make('Statistics')
                    ->schema([
                        TextEntry::make('students_count')
                            ->label('Total Students')
                            ->badge()
                            ->color('primary')
                            ->getStateUsing(fn ($record) => $record->students()->count()),

                        TextEntry::make('enrollments_count')
                            ->label('Active Enrollments')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(fn ($record) => $record->enrollments()->where('status', 'enrolled')->count()),

                        TextEntry::make('sections_count')
                            ->label('Total Sections')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(fn ($record) => $record->sections()->count()),
                    ])
                    ->columns(3),

                Section::make('System Information')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime()
                            ->placeholder('-')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->placeholder('-')
                            ->icon('heroicon-o-clock')
                            ->since(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
