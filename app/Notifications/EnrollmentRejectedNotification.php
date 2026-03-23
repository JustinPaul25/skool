<?php

namespace App\Notifications;

use App\Models\EnrollmentApplication;
use App\Models\User;
use App\Notifications\Concerns\QueuesOnNotificationsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentRejectedNotification extends Notification implements ShouldQueue
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
        $application = $this->enrollmentApplication->loadMissing(['branch', 'schoolYear']);

        return (new MailMessage)
            ->subject(__('Enrollment application update'))
            ->greeting(__('Hello!'))
            ->line(__('We regret to inform you that your enrollment application could not be approved at this time.'))
            ->line(__('Branch: :name', ['name' => $application->branch?->name ?? '—']))
            ->line(__('School year: :name', ['name' => $application->schoolYear?->name ?? '—']))
            ->line(__('If you have questions, please contact the school office.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Enrollment not approved'),
            'body' => __('Your enrollment application was not approved.'),
            'enrollment_application_id' => $this->enrollmentApplication->id,
        ];
    }
}
