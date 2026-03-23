<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardBranchScope;
use App\Models\Payment;
use App\Models\SchoolYear;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class RevenueTimeSeriesChart extends ChartWidget
{
    use InteractsWithDashboardBranchScope;

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '320px';

    protected string $color = 'success';

    public function getHeading(): string|Htmlable|null
    {
        return __('Monthly revenue');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('Payment totals by month for the active school year.');
    }

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $activeYear = SchoolYear::appCurrent();

        if ($activeYear === null) {
            return [
                'datasets' => [
                    [
                        'label' => __('Revenue (₱)'),
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $branchId = $this->scopedBranchId();

        $payments = Payment::query()
            ->whereNotNull('paid_at')
            ->whereHas('account', fn ($q) => $q->whereHas('student', function ($s) use ($branchId) {
                $s->when($branchId, fn ($q2) => $q2->where('branch_id', $branchId));
            }))
            ->whereBetween('paid_at', [
                $activeYear->start_date->copy()->startOfDay(),
                $activeYear->end_date->copy()->endOfDay(),
            ])
            ->get(['paid_at', 'amount']);

        $sums = $payments->groupBy(fn (Payment $p) => $p->paid_at?->format('Y-m'))
            ->map(fn ($group) => round((float) $group->sum('amount'), 2));

        $labels = [];
        $data = [];

        $cursor = $activeYear->start_date->copy()->startOfMonth();
        $end = $activeYear->end_date->copy()->endOfMonth();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $data[] = (float) ($sums[$key] ?? 0);
            $cursor->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => __('Revenue (₱)'),
                    'data' => $data,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
