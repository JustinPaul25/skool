<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\SchoolYear;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BranchOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -3;

    protected ?string $pollingInterval = null;

    protected function getHeading(): ?string
    {
        $branch = auth()->user()?->branch;

        if ($branch instanceof Branch) {
            return __('Branch overview: :name', ['name' => $branch->name]);
        }

        return __('Branch overview');
    }

    protected function getDescription(): ?string
    {
        $year = SchoolYear::appCurrent();

        return $year
            ? __('Active school year: :name', ['name' => $year->name])
            : __('No active school year is set.');
    }

    /**
     * Replaced by {@see DashboardStatsOverviewWidget} (Phase 8.1) which includes branch-scoped stats.
     */
    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $branchId = $user?->branch_id;

        if ($branchId === null) {
            return [];
        }

        $activeYear = SchoolYear::appCurrent();

        $enrollmentCount = $activeYear
            ? Enrollment::query()
                ->where('branch_id', $branchId)
                ->where('school_year_id', $activeYear->id)
                ->where('status', 'enrolled')
                ->count()
            : 0;

        $revenue = '0.00';
        if ($activeYear) {
            $revenue = (string) (Payment::query()
                ->whereHas('account', fn ($q) => $q->whereHas('student', fn ($s) => $s->where('branch_id', $branchId)))
                ->where(function ($q) use ($activeYear) {
                    $q->whereHas('enrollment', fn ($e) => $e->where('school_year_id', $activeYear->id))
                        ->orWhere(function ($q2) use ($activeYear) {
                            $q2->whereNull('enrollment_id')
                                ->whereBetween('paid_at', [
                                    $activeYear->start_date->startOfDay(),
                                    $activeYear->end_date->endOfDay(),
                                ]);
                        });
                })
                ->sum('amount') ?? '0');
        }

        $outstanding = (string) (Account::query()
            ->whereHas('student', fn ($q) => $q->where('branch_id', $branchId))
            ->sum('balance') ?? '0');

        return [
            Stat::make(__('Enrolled students'), number_format($enrollmentCount))
                ->description(__('In the active school year'))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make(__('Revenue (active year)'), '₱'.number_format((float) $revenue, 2))
                ->description(__('Sum of recorded payments'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
            Stat::make(__('Outstanding balances'), '₱'.number_format((float) $outstanding, 2))
                ->description(__('Total amount still owed'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
