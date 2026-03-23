<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Activity'))
                    ->schema([
                        TextEntry::make('event')
                            ->placeholder('—'),

                        TextEntry::make('description')
                            ->columnSpanFull(),

                        TextEntry::make('log_name')
                            ->label(__('Log name'))
                            ->placeholder('—'),

                        TextEntry::make('subject_type')
                            ->label(__('Subject type'))
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),

                        TextEntry::make('subject_id')
                            ->label(__('Subject ID'))
                            ->placeholder('—'),

                        TextEntry::make('causer.name')
                            ->label(__('Causer'))
                            ->placeholder('—'),

                        TextEntry::make('batch_uuid')
                            ->label(__('Batch'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('Properties'))
                    ->schema([
                        TextEntry::make('properties')
                            ->label(__('Changes / properties'))
                            ->formatStateUsing(function ($state): string {
                                if ($state === null) {
                                    return '—';
                                }

                                if ($state instanceof Collection) {
                                    $state = $state->toArray();
                                }

                                return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make(__('Timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
