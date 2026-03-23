<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\InteractsWithDashboardBranchScope;
use App\Models\Enrollment;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class EnrollmentsByGradeLevelChart extends ChartWidget
{
    use InteractsWithDashboardBranchScope;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '320px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Enrollments by grade level');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('Enrolled students in the active school year, grouped by grade level.');
    }

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $activeYear = SchoolYear::appCurrent();

        if ($activeYear === null) {
            return [
                'datasets' => [
                    [
                        'label' => __('Enrollments'),
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $branchId = $this->scopedBranchId();

        $counts = Enrollment::query()
            ->where('school_year_id', $activeYear->id)
            ->where('status', 'enrolled')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('grade_level_id, count(*) as aggregate')
            ->groupBy('grade_level_id')
            ->pluck('aggregate', 'grade_level_id');

        $levels = GradeLevel::query()
            ->when($branchId, fn ($q) => $q->where(function ($q2) use ($branchId) {
                $q2->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $labels = [];
        $data = [];

        foreach ($levels as $level) {
            $labels[] = $level->name;
            $data[] = (int) ($counts[$level->id] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('Enrolled'),
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
