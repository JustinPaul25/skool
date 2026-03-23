<?php

use App\Filament\Pages\SettingsPage;
use App\Models\SchoolSetting;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\SettingsService;

it('restricts Filament settings page to administrators', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrator');
    $this->actingAs($admin);
    expect(SettingsPage::canAccess())->toBeTrue();

    $staff = User::factory()->create();
    $staff->assignRole('staff');
    $this->actingAs($staff);
    expect(SettingsPage::canAccess())->toBeFalse();
});

it('forbids non-administrators from loading the settings page', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->actingAs($staff)
        ->get('/admin/settings')
        ->assertForbidden();
});

it('updates school settings through the settings service', function () {
    $year = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => now()->subYear(),
        'end_date' => now()->addYear(),
        'is_active' => false,
    ]);

    $setting = SchoolSetting::instance();

    app(SettingsService::class)->update([
        'school_name' => 'Test Academy',
        'address' => '123 Main St',
        'phone' => '555-0100',
        'active_school_year_id' => $year->id,
        'email_footer_text' => 'Thank you.',
    ]);

    $setting->refresh();

    expect($setting->school_name)->toBe('Test Academy')
        ->and($setting->address)->toBe('123 Main St')
        ->and($setting->phone)->toBe('555-0100')
        ->and($setting->active_school_year_id)->toBe($year->id)
        ->and($setting->email_footer_text)->toBe('Thank you.');
});

it('uses the settings override for the app current school year', function () {
    $flagged = SchoolYear::query()->create([
        'name' => 'Flagged active',
        'start_date' => now()->subMonths(6),
        'end_date' => now()->addMonths(6),
        'is_active' => true,
    ]);

    $override = SchoolYear::query()->create([
        'name' => 'Override year',
        'start_date' => now()->subYear(),
        'end_date' => now()->addYear(),
        'is_active' => false,
    ]);

    SchoolSetting::instance()->update(['active_school_year_id' => $override->id]);

    expect(SchoolYear::appCurrent()?->id)->toBe($override->id)
        ->and(SchoolYear::appCurrent()?->id)->not->toBe($flagged->id);
});

it('shares school branding on inertia responses', function () {
    SchoolSetting::instance()->update([
        'school_name' => 'Shared School',
        'address' => null,
        'phone' => null,
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('school')
            ->where('school.name', 'Shared School'));
});
