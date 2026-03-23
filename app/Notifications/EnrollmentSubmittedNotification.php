<?php

namespace App\Notifications;

use App\Models\EnrollmentApplication;
use App\Models\User;
use App\Notifications\Concerns\QueuesOnNotificationsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentSubmittedNotification extends Notification implements ShouldQueue
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
        $application = $this->enrollmentApplication->loadMissing(['branch', 'schoolYear', 'gradeLevel', 'student']);

        $reference = 'ENR-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT);

        return (new MailMessage)
            ->subject(__('Enrollment application received — :ref', ['ref' => $reference]))
            ->greeting(__('Hello!'))
            ->line(__('We have received your online enrollment application.'))
            ->line(__('Reference number: :ref', ['ref' => $reference]))
            ->line(__('Branch: :name', ['name' => $application->branch?->name ?? '—']))
            ->line(__('School year: :name', ['name' => $application->schoolYear?->name ?? '—']))
            ->line(__('Grade level: :name', ['name' => $application->gradeLevel?->name ?? '—']))
            ->line(__('We will contact you if further information is required.'))
            ->salutation(__('Thank you'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $reference = 'ENR-'.str_pad((string) $this->enrollmentApplication->id, 6, '0', STR_PAD_LEFT);

        return [
            'title' => __('Enrollment submitted'),
            'body' => __('We received your application (:ref).', ['ref' => $reference]),
            'enrollment_application_id' => $this->enrollmentApplication->id,
        ];
    }
}
