<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class RecentActivityWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Recent activity'))
            ->description(__('Last 10 entries from the activity log.'))
            ->query(fn (): Builder => Activity::query()->latest()->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('event')
                    ->label(__('Event'))
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('subject_type')
                    ->label(__('Subject'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),
                TextColumn::make('subject_id')
                    ->label(__('Subject ID'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('causer.name')
                    ->label(__('Causer'))
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label(__('When'))
                    ->dateTime()
                    ->sortable(false),
            ]);
    }

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user !== null
            && $user->hasAnyRole(['administrator', 'staff']);
    }
}
