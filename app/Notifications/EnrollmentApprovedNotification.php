<?php

namespace App\Notifications;

use App\Models\EnrollmentApplication;
use App\Models\User;
use App\Notifications\Concerns\QueuesOnNotificationsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesOnNotificationsChannel;

    public function __construct(public EnrollmentApplication $enrollmentApplication)
    {
        $this->queueOnNotificationsChannel();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if ($notifiable instanceof User) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->enrollmentApplication->loadMissing(['branch', 'schoolYear', 'gradeLevel']);

        return (new MailMessage)
            ->subject(__('Enrollment application approved'))
            ->greeting(__('Hello!'))
            ->line(__('Your enrollment application has been approved.'))
            ->line(__('Branch: :name', ['name' => $application->branch?->name ?? '—']))
            ->line(__('School year: :name', ['name' => $application->schoolYear?->name ?? '—']))
            ->line(__('Grade level: :name', ['name' => $application->gradeLevel?->name ?? '—']))
            ->line(__('Thank you for choosing our school.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Enrollment approved'),
            'body' => __('Your enrollment application has been approved.'),
            'enrollment_application_id' => $this->enrollmentApplication->id,
        ];
    }
}
