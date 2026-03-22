<?php

namespace App\Filament\Resources\EnrollmentApplications\Tables;

use App\Models\EnrollmentApplication;
use App\Services\EnrollmentApplicationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;

class EnrollmentApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('applicant_name')
                    ->label('Applicant')
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->where(function (Builder $q) use ($search): void {
                            $q->whereHas('student', function (Builder $studentQuery) use ($search): void {
                                $studentQuery
                                    ->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('middle_name', 'like', "%{$search}%")
                                    ->orWhere('student_id', 'like', "%{$search}%");
                            })->orWhere('id', $search);
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): void {
                        $query->orderByRaw('(
                            select last_name from students
                            where students.id = enrollment_applications.student_id
                            limit 1
                        ) '.$direction);
                    }),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gradeLevel.name')
                    ->label('Grade level')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('schoolYear.name')
                    ->label('School year')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('reviewer.name')
                    ->label('Reviewed by')
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
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('school_year_id')
                    ->relationship('schoolYear', 'name')
                    ->label('School year')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('grade_level_id')
                    ->relationship('gradeLevel', 'name')
                    ->label('Grade level')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->authorize('approve')
                    ->visible(fn (EnrollmentApplication $record): bool => in_array($record->status, ['submitted', 'under_review'], true))
                    ->schema([
                        Textarea::make('notes')
                            ->label('Optional notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, EnrollmentApplication $record): void {
                        try {
                            app(EnrollmentApplicationService::class)->approve(
                                $record,
                                auth()->user(),
                                $data['notes'] ?? null,
                            );

                            Notification::make()
                                ->title('Application approved')
                                ->success()
                                ->send();
                        } catch (InvalidArgumentException $e) {
                            Notification::make()
                                ->title('Unable to approve')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $e) {
                            report($e);

                            Notification::make()
                                ->title('Unable to approve')
                                ->body('An unexpected error occurred.')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->authorize('reject')
                    ->visible(fn (EnrollmentApplication $record): bool => in_array($record->status, ['submitted', 'under_review'], true))
                    ->schema([
                        Textarea::make('rejection_notes')
                            ->label('Rejection notes')
                            ->helperText('Required — explain why this application is rejected.')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, EnrollmentApplication $record): void {
                        try {
                            app(EnrollmentApplicationService::class)->reject(
                                $record,
                                auth()->user(),
                                $data['rejection_notes'] ?? '',
                            );

                            Notification::make()
                                ->title('Application rejected')
                                ->success()
                                ->send();
                        } catch (InvalidArgumentException $e) {
                            Notification::make()
                                ->title('Unable to reject')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (Throwable $e) {
                            report($e);

                            Notification::make()
                                ->title('Unable to reject')
                                ->body('An unexpected error occurred.')
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve selected applications')
                        ->modalDescription('Applications without a linked student will be skipped. Optional notes are not applied in bulk approve.')
                        ->authorizeIndividualRecords('approve')
                        ->action(function (Collection $records): void {
                            $service = app(EnrollmentApplicationService::class);
                            $reviewer = auth()->user();
                            $approved = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                try {
                                    $service->approve($record, $reviewer, null);
                                    $approved++;
                                } catch (InvalidArgumentException) {
                                    $failed++;
                                } catch (Throwable $e) {
                                    report($e);
                                    $failed++;
                                }
                            }

                            Notification::make()
                                ->title($approved.' application(s) approved'.($failed > 0 ? " ({$failed} skipped)" : ''))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
