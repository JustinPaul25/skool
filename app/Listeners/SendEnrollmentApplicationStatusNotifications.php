<?php

namespace App\Listeners;

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRejected;
use App\Notifications\EnrollmentApprovedNotification;
use App\Notifications\EnrollmentRejectedNotification;
use Illuminate\Support\Facades\Notification;

class SendEnrollmentApplicationStatusNotifications
{
    public function handleApproved(EnrollmentApproved $event): void
    {
        $application = $event->enrollmentApplication->loadMissing('student.user');
        $student = $application->student;

        if (! $student) {
            return;
        }

        $notification = new EnrollmentApprovedNotification($application);

        if ($student->user) {
            $student->user->notify($notification);

            return;
        }

        if ($student->email) {
            Notification::route('mail', $student->email)->notify($notification);
        }
    }

    public function handleRejected(EnrollmentRejected $event): void
    {
        $application = $event->enrollmentApplication->loadMissing('student.user');
        $student = $application->student;

        if (! $student) {
            return;
        }

        $notification = new EnrollmentRejectedNotification($application);

        if ($student->user) {
            $student->user->notify($notification);

            return;
        }

        if ($student->email) {
            Notification::route('mail', $student->email)->notify($notification);
        }
    }
}
