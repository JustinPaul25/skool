<?php

namespace App\Filament\Resources\SchoolYears\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolYearInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('School Year Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('School Year')
                            ->weight('bold')
                            ->size('lg'),

                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),

                        TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date('F j, Y')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('end_date')
                            ->label('End Date')
                            ->date('F j, Y')
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(2),

                Section::make('Statistics')
                    ->schema([
                        TextEntry::make('enrollments_count')
                            ->label('Total Enrollments')
                            ->badge()
                            ->color('primary')
                            ->getStateUsing(fn ($record) => $record->enrollments()->count()),

                        TextEntry::make('active_enrollments_count')
                            ->label('Active Enrollments')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(fn ($record) => $record->enrollments()->where('status', 'enrolled')->count()),

                        TextEntry::make('payment_utilities_count')
                            ->label('Fee Configurations')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(fn ($record) => $record->paymentUtilities()->count()),
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
