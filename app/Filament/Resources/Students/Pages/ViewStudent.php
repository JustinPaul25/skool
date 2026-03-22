<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Jobs\GenerateReportCardJob;
use App\Models\SchoolYear;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('generateReportCard')
                ->label('Generate report card')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->authorize(function (): bool {
                    /** @var User|null $user */
                    $user = Auth::user();

                    return $user !== null && $user->can('view', $this->record);
                })
                ->visible(fn (): bool => $this->record->enrollments()->exists())
                ->schema([
                    Select::make('school_year_id')
                        ->label('School year')
                        ->options(function (): array {
                            $ids = $this->record->enrollments()->pluck('school_year_id')->unique()->filter();

                            return SchoolYear::query()
                                ->whereIn('id', $ids)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->required()
                        ->native(false),
                ])
                ->modalHeading('Generate report card')
                ->modalDescription('Builds a PDF from recorded grades, stores it on your configured media disk (e.g. DigitalOcean Spaces), and notifies the linked student user when ready.')
                ->action(function (array $data): void {
                    $schoolYearId = (int) $data['school_year_id'];

                    GenerateReportCardJob::dispatch($this->record->id, $schoolYearId);

                    Notification::make()
                        ->title('Report card queued')
                        ->body('The report card will be generated in the background. The student will be notified when it is ready.')
                        ->success()
                        ->send();
                }),

            Action::make('exportData')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): void {
                    Notification::make()
                        ->title('Export')
                        ->body('Excel export classes (e.g. StudentsExport) can be added alongside maatwebsite/excel in a later step.')
                        ->info()
                        ->send();
                }),
        ];
    }
}
