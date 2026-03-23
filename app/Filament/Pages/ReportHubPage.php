<?php

namespace App\Filament\Pages;

use App\Exports\GradesExport;
use App\Exports\PaymentsExport;
use App\Exports\StudentsExport;
use App\Jobs\BulkReportCardJob;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class ReportHubPage extends Page
{
    protected static ?string $slug = 'report-hub';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Report hub');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null
            && $user->hasAnyRole(['administrator', 'staff', 'branch_manager']);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Export students'))
                    ->description(__('Download student records as a spreadsheet (Excel / XLSX).'))
                    ->footer([
                        Action::make('exportStudents')
                            ->label(__('Download Excel'))
                            ->icon(Heroicon::OutlinedArrowDownTray)
                            ->color('gray')
                            ->action(function (): mixed {
                                $filename = 'students-'.now()->format('Y-m-d_His').'.xlsx';

                                return Excel::download(new StudentsExport, $filename);
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make(__('Export payments'))
                    ->description(__('Download payment history as a spreadsheet. Branch managers only see their branch.'))
                    ->footer([
                        Action::make('exportPayments')
                            ->label(__('Download Excel'))
                            ->icon(Heroicon::OutlinedArrowDownTray)
                            ->color('gray')
                            ->action(function (): mixed {
                                $filename = 'payments-'.now()->format('Y-m-d_His').'.xlsx';

                                return Excel::download(new PaymentsExport, $filename);
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make(__('Export grades'))
                    ->description(__('Download grade rows with student, subject, and period. Branch managers only see their branch.'))
                    ->footer([
                        Action::make('exportGrades')
                            ->label(__('Download Excel'))
                            ->icon(Heroicon::OutlinedArrowDownTray)
                            ->color('gray')
                            ->action(function (): mixed {
                                $filename = 'grades-'.now()->format('Y-m-d_His').'.xlsx';

                                return Excel::download(new GradesExport, $filename);
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make(__('Generate report cards (bulk)'))
                    ->description(__('Queue PDF report cards for every enrolled student in the active school year. Each student receives a separate queued job. Branch managers only queue students in their branch.'))
                    ->footer([
                        Action::make('bulkReportCards')
                            ->label(__('Queue bulk generation'))
                            ->icon(Heroicon::OutlinedDocumentDuplicate)
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading(__('Queue report card generation?'))
                            ->modalDescription(function (): string {
                                $year = SchoolYear::appCurrent();
                                if ($year === null) {
                                    return __('No active school year is set. Set one before running this action.');
                                }

                                $user = auth()->user();
                                $branchId = $user?->hasRole('branch_manager') ? $user->branch_id : null;

                                $count = (int) Enrollment::query()
                                    ->where('school_year_id', $year->id)
                                    ->where('status', 'enrolled')
                                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                    ->clone()
                                    ->toBase()
                                    ->selectRaw('count(distinct enrollments.student_id) as aggregate')
                                    ->value('aggregate');

                                return __('This will queue :count report card job(s) for :year.', [
                                    'count' => $count,
                                    'year' => $year->name,
                                ]);
                            })
                            ->action(function (): void {
                                $year = SchoolYear::appCurrent();

                                if ($year === null) {
                                    Notification::make()
                                        ->title(__('No active school year'))
                                        ->body(__('Set an active school year before generating report cards.'))
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $user = auth()->user();
                                $branchId = $user?->hasRole('branch_manager') ? $user->branch_id : null;

                                if ($user?->hasRole('branch_manager') && $branchId === null) {
                                    Notification::make()
                                        ->title(__('Branch required'))
                                        ->body(__('Your account is not linked to a branch.'))
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                BulkReportCardJob::dispatch($year->id, $branchId);

                                Notification::make()
                                    ->title(__('Report cards queued'))
                                    ->body(__('Individual PDF jobs have been dispatched to the queue.'))
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
