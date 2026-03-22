<?php

namespace App\Listeners;

use App\Events\EnrollmentSubmitted;
use App\Notifications\EnrollmentSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class SendEnrollmentSubmittedNotification
{
    public function handle(EnrollmentSubmitted $event): void
    {
        $application = $event->enrollmentApplication->loadMissing('student');
        $email = $application->student?->email;

        if (! $email) {
            return;
        }

        Notification::route('mail', $email)
            ->notify(new EnrollmentSubmittedNotification($application));
    }
}
