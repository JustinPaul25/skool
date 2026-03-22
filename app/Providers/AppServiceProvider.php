<?php

namespace App\Providers;

use App\Events\EnrollmentApproved;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use App\Events\EnrollmentRejected;
use App\Events\EnrollmentSubmitted;
use App\Filesystem\WindowsSafeFilesystem;
use App\Listeners\SendEnrollmentApplicationStatusNotifications;
use App\Listeners\SendEnrollmentSubmittedNotification;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
        RedirectIfAuthenticated::redirectUsing(function ($request): string {
            if ($request->user()?->hasRole('student')) {
                return route('portal.dashboard');
            }

            return url('/admin');
        });

        Event::listen(EnrollmentApproved::class, [SendEnrollmentApplicationStatusNotifications::class, 'handleApproved']);
        Event::listen(EnrollmentRejected::class, [SendEnrollmentApplicationStatusNotifications::class, 'handleRejected']);
        Event::listen(EnrollmentSubmitted::class, SendEnrollmentSubmittedNotification::class);
    }
}
