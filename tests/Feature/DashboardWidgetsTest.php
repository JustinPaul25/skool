<?php

use App\Filament\Widgets\DashboardStatsOverviewWidget;
use App\Filament\Widgets\EnrollmentsByGradeLevelChart;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RevenueTimeSeriesChart;
use App\Models\Branch;
use App\Models\User;

it('shows dashboard stats and charts for branch managers', function () {
    $branch = Branch::query()->create([
        'name' => 'East',
        'code' => 'E-'.uniqid(),
        'address' => '1 Main St',
        'phone' => '1',
        'email' => 'e@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $manager = User::factory()->create(['branch_id' => $branch->id]);
    $manager->assignRole('branch_manager');

    $this->actingAs($manager);

    expect(DashboardStatsOverviewWidget::canView())->toBeTrue()
        ->and(EnrollmentsByGradeLevelChart::canView())->toBeTrue()
        ->and(RevenueTimeSeriesChart::canView())->toBeTrue()
        ->and(RecentActivityWidget::canView())->toBeFalse();
});

it('shows recent activity widget for administrators and staff', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');
    $this->actingAs($admin);
    expect(RecentActivityWidget::canView())->toBeTrue();

    $staff = User::factory()->create();
    $staff->assignRole('staff');
    $this->actingAs($staff);
    expect(RecentActivityWidget::canView())->toBeTrue();
});
