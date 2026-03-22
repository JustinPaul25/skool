<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Services\PaymentReceiptService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_no')
                    ->label('OR #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),

                TextColumn::make('account.student.full_name')
                    ->label('Student')
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->whereHas('account.student', function (Builder $q) use ($search): void {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('student_id', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable()
                    ->alignEnd()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->money('PHP')
                    ),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->color(fn (string $state): string => match ($state) {
                        'tuition' => 'primary',
                        'miscellaneous' => 'info',
                        'discount' => 'warning',
                        'penalty' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('receiver.name')
                    ->label('Received by')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'tuition' => 'Tuition',
                        'miscellaneous' => 'Miscellaneous',
                        'discount' => 'Discount',
                        'penalty' => 'Penalty',
                    ])
                    ->label('Type'),

                Filter::make('branch')
                    ->schema([
                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $branchId = $data['branch_id'] ?? null;
                        if (blank($branchId)) {
                            return;
                        }

                        $query->whereHas('account.student', fn (Builder $q) => $q->where('branch_id', $branchId));
                    }),

                Filter::make('school_year')
                    ->schema([
                        Select::make('school_year_id')
                            ->label('School year')
                            ->options(fn (): array => SchoolYear::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $schoolYearId = $data['school_year_id'] ?? null;
                        if (blank($schoolYearId)) {
                            return;
                        }

                        $query->whereHas('enrollment', fn (Builder $q) => $q->where('school_year_id', $schoolYearId));
                    }),

                Filter::make('paid_between')
                    ->schema([
                        DatePicker::make('paid_from')->label('From'),
                        DatePicker::make('paid_until')->label('Until'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): void {
                        if (filled($data['paid_from'] ?? null)) {
                            $query->whereDate('paid_at', '>=', $data['paid_from']);
                        }

                        if (filled($data['paid_until'] ?? null)) {
                            $query->whereDate('paid_at', '<=', $data['paid_until']);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('print_or')
                    ->label('Print OR')
                    ->icon('heroicon-o-printer')
                    ->authorize('view')
                    ->action(fn (Payment $record) => app(PaymentReceiptService::class)->download($record)),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('paid_at', 'desc');
    }
}
