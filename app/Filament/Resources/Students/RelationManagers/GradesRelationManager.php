<?php

namespace App\Filament\Resources\Students\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GradesRelationManager extends RelationManager
{
    protected static string $relationship = 'grades';

    protected static ?string $title = 'Grades';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->join('subjects as subject', 'subject.id', '=', 'grades.subject_id');
            })
            ->columns([
                TextColumn::make('enrollment.schoolYear.name')
                    ->label('School year')
                    ->toggleable(),

                TextColumn::make('subject.code')
                    ->label('Subject code')
                    ->badge()
                    ->color('info'),

                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->wrap(),

                TextColumn::make('period')
                    ->badge(),

                TextColumn::make('score')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('remarks')
                    ->placeholder('—')
                    ->limit(40),

                TextColumn::make('grader.name')
                    ->label('Graded by')
                    ->placeholder('—'),
            ])
            ->defaultSort('subject.code');
    }
}
