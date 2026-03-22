<?php

namespace App\Notifications;

use App\Models\EnrollmentApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentApplicationApprovedNotification extends Notification implements ShouldQueue
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
        $application = $this->enrollmentApplication->loadMissing(['branch', 'schoolYear', 'gradeLevel']);

        return (new MailMessage)
            ->subject('Enrollment application approved')
            ->greeting('Hello!')
            ->line('Your enrollment application has been approved.')
            ->line('Branch: '.$application->branch?->name)
            ->line('School year: '.$application->schoolYear?->name)
            ->line('Grade level: '.$application->gradeLevel?->name)
            ->line('Thank you for choosing our school.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Enrollment approved',
            'body' => 'Your enrollment application has been approved.',
            'enrollment_application_id' => $this->enrollmentApplication->id,
        ];
    }
}
