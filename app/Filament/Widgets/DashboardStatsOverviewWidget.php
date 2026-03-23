<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardBranchScope;
use App\Models\Account;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverviewWidget extends StatsOverviewWidget
{
    use InteractsWithDashboardBranchScope;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getHeading(): ?string
    {
        return __('School overview');
    }

    protected function getDescription(): ?string
    {
        $year = SchoolYear::appCurrent();

        return $year
            ? __('Active school year: :name', ['name' => $year->name])
            : __('No active school year is set.');
    }

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    protected function getStats(): array
    {
        $branchId = $this->scopedBranchId();
        $activeYear = SchoolYear::appCurrent();

        $totalStudents = Student::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        $enrolledThisYear = $activeYear
            ? Enrollment::query()
                ->where('school_year_id', $activeYear->id)
                ->where('status', 'enrolled')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->count()
            : 0;

        $revenue = '0.00';
        if ($activeYear) {
            $revenue = (string) (Payment::query()
                ->whereHas('account', fn ($q) => $q->whereHas('student', function ($s) use ($branchId) {
                    $s->when($branchId, fn ($q2) => $q2->where('branch_id', $branchId));
                }))
                ->where(function ($q) use ($activeYear) {
                    $q->whereHas('enrollment', fn ($e) => $e->where('school_year_id', $activeYear->id))
                        ->orWhere(function ($q2) use ($activeYear) {
                            $q2->whereNull('enrollment_id')
                                ->whereBetween('paid_at', [
                                    $activeYear->start_date->copy()->startOfDay(),
                                    $activeYear->end_date->copy()->endOfDay(),
                                ]);
                        });
                })
                ->sum('amount') ?? '0');
        }

        $outstanding = (string) (Account::query()
            ->whereHas('student', function ($q) use ($branchId) {
                $q->when($branchId, fn ($q2) => $q2->where('branch_id', $branchId));
            })
            ->sum('balance') ?? '0');

        return [
            Stat::make(__('Total students'), number_format($totalStudents))
                ->description(__('All student records'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('gray'),
            Stat::make(__('Enrolled this year'), number_format($enrolledThisYear))
                ->description(__('Active school year — enrolled status'))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make(__('Revenue (active year)'), '₱'.number_format((float) $revenue, 2))
                ->description(__('Recorded payments for the active year'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
            Stat::make(__('Outstanding balances'), '₱'.number_format((float) $outstanding, 2))
                ->description(__('Total amount still owed'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
