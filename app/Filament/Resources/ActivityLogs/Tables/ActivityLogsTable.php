<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event')
                    ->label(__('Event'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('subject_type')
                    ->label(__('Subject type'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->where('subject_type', 'like', '%'.$search.'%');
                    })
                    ->sortable(),

                TextColumn::make('subject_id')
                    ->label(__('Subject ID'))
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('causer.name')
                    ->label(__('Causer'))
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('When'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label(__('Event'))
                    ->options(fn (): array => Activity::query()
                        ->whereNotNull('event')
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all())
                    ->searchable(),

                SelectFilter::make('causer_id')
                    ->label(__('Causer'))
                    ->options(fn (): array => User::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query
                            ->where('causer_type', User::class)
                            ->where('causer_id', $value);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
