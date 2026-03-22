<?php

namespace App\Notifications;

use App\Models\EnrollmentApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentApplicationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EnrollmentApplication $enrollmentApplication) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if ($notifiable instanceof \App\Models\User) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->enrollmentApplication->loadMissing(['branch', 'schoolYear']);

        return (new MailMessage)
            ->subject('Enrollment application update')
            ->greeting('Hello!')
            ->line('We regret to inform you that your enrollment application could not be approved at this time.')
            ->line('Branch: '.$application->branch?->name)
            ->line('School year: '.$application->schoolYear?->name)
            ->line('If you have questions, please contact the school office.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Enrollment not approved',
            'body' => 'Your enrollment application was not approved.',
            'enrollment_application_id' => $this->enrollmentApplication->id,
        ];
    }
}
