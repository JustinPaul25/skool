<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use App\Jobs\GradeImportJob;
use App\Models\Grade;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListGrades extends ListRecords
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importGrades')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import grades from CSV')
                ->modalDescription('Header row must be exactly: enrollment_id,subject_id,period,score,remarks. Period: q1, q2, q3, q4, or final. Score: 0–100.')
                ->authorize('create', Grade::class)
                ->schema([
                    FileUpload::make('file')
                        ->label('CSV file')
                        ->disk((string) config('filesystems.uploads_disk', 'spaces'))
                        ->directory('grade-imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                        ->required()
                        ->helperText('Existing rows (same enrollment, subject, period) are updated.'),
                ])
                ->action(function (array $data): void {
                    $path = $data['file'] ?? null;
                    if (! is_string($path) || $path === '') {
                        Notification::make()
                            ->title('No file selected')
                            ->danger()
                            ->send();

                        return;
                    }

                    GradeImportJob::dispatch(Auth::id(), $path);

                    Notification::make()
                        ->title('Import queued')
                        ->body('You will receive a database notification when processing finishes.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
