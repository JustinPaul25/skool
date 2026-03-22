<?php

namespace App\Notifications;

use App\Models\EnrollmentApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EnrollmentApplication $enrollmentApplication) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->enrollmentApplication->loadMissing(['branch', 'schoolYear', 'gradeLevel', 'student']);

        $reference = 'ENR-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT);

        return (new MailMessage)
            ->subject('Enrollment application received — '.$reference)
            ->greeting('Hello!')
            ->line('We have received your online enrollment application.')
            ->line('Reference number: '.$reference)
            ->line('Branch: '.$application->branch?->name)
            ->line('School year: '.$application->schoolYear?->name)
            ->line('Grade level: '.$application->gradeLevel?->name)
            ->line('We will contact you if further information is required.')
            ->salutation('Thank you');
    }
}
