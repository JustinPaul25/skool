<?php

use App\Filament\Widgets\AdminAccountWidget;
use App\Filament\Widgets\AdminFilamentInfoWidget;
use App\Filament\Widgets\BranchOverviewWidget;
use App\Filament\Widgets\DashboardStatsOverviewWidget;
use App\Models\Branch;
use App\Models\User;

it('uses the consolidated dashboard stats widget instead of the legacy branch overview widget', function () {
    $branch = Branch::query()->create([
        'name' => 'North',
        'code' => 'N-'.uniqid(),
        'address' => '1 Main St',
        'phone' => '1',
        'email' => 'n@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $manager = User::factory()->create(['branch_id' => $branch->id]);
    $manager->assignRole('branch_manager');

    $this->actingAs($manager);

    expect(BranchOverviewWidget::canView())->toBeFalse()
        ->and(DashboardStatsOverviewWidget::canView())->toBeTrue();
});

it('hides legacy branch overview widget for administrators', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $this->actingAs($admin);

    expect(BranchOverviewWidget::canView())->toBeFalse();
});

it('hides default account and filament info widgets for branch managers', function () {
    $branch = Branch::query()->create([
        'name' => 'South',
        'code' => 'S-'.uniqid(),
        'address' => '1 Main St',
        'phone' => '1',
        'email' => 's@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $manager = User::factory()->create(['branch_id' => $branch->id]);
    $manager->assignRole('branch_manager');

    $this->actingAs($manager);

    expect(AdminAccountWidget::canView())->toBeFalse()
        ->and(AdminFilamentInfoWidget::canView())->toBeFalse();
});

it('shows account and filament info widgets for staff', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->actingAs($staff);

    expect(AdminAccountWidget::canView())->toBeTrue()
        ->and(AdminFilamentInfoWidget::canView())->toBeTrue();
});
