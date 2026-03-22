<?php

namespace App\Filament\Resources\GradeLevels\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GradeLevelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('order')
                    ->numeric(),
                TextEntry::make('branch.name')
                    ->label('Branch')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
