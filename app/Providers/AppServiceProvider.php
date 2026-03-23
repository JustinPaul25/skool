<?php

namespace App\Providers;

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRejected;
use App\Events\EnrollmentSubmitted;
use App\Events\PaymentReceived;
use App\Filesystem\WindowsSafeFilesystem;
use App\Listeners\SendEnrollmentApplicationStatusNotifications;
use App\Listeners\SendEnrollmentSubmittedNotification;
use App\Listeners\SendPaymentReceivedNotification;
use App\Models\Enrollment;
use App\Models\SchoolSetting;
use App\Policies\ActivityLogPolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\SchoolSettingPolicy;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->app->singleton('files', function (): Filesystem {
                return new WindowsSafeFilesystem;
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityLogPolicy::class);
        Gate::policy(SchoolSetting::class, SchoolSettingPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        RedirectIfAuthenticated::redirectUsing(function ($request): string {
            if ($request->user()?->hasRole('student')) {
                return route('portal.dashboard');
            }

            return url('/admin');
        });

        Event::listen(EnrollmentApproved::class, [SendEnrollmentApplicationStatusNotifications::class, 'handleApproved']);
        Event::listen(EnrollmentRejected::class, [SendEnrollmentApplicationStatusNotifications::class, 'handleRejected']);
        Event::listen(EnrollmentSubmitted::class, SendEnrollmentSubmittedNotification::class);
        Event::listen(PaymentReceived::class, SendPaymentReceivedNotification::class);
    }
}
