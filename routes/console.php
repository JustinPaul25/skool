<?php

use App\Jobs\SendPaymentReminderJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Phase 13 — Jobs & Background Processing
Schedule::job(new SendPaymentReminderJob)->weekly();

// Horizon snapshot only if Horizon is installed.
if (class_exists('Laravel\\Horizon\\Horizon')) {
    Schedule::command('horizon:snapshot')->daily();
}
