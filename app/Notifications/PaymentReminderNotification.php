<?php

namespace App\Notifications;

use App\Models\Account;
use App\Notifications\Concerns\QueuesOnNotificationsChannel;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesOnNotificationsChannel;

    /**
     * Send a delayed payment reminder email (queued via {@see NotificationFacade::later} semantics).
     */
    public function __construct(
        public Account $account,
        ?DateTimeInterface $sendAt = null,
    ) {
        $this->queueOnNotificationsChannel();
        $this->delay($sendAt ?? now()->addDay());
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->account->loadMissing(['student.branch']);
        $student = $this->account->student;

        return (new MailMessage)
            ->subject(__('Payment reminder'))
            ->greeting(__('Hello!'))
            ->line(__('This is a friendly reminder about an outstanding balance for :name.', [
                'name' => $student?->getFullName() ?? __('a student account'),
            ]))
            ->line(__('Current balance: :balance', [
                'balance' => number_format((float) $this->account->balance, 2),
            ]))
            ->line(__('Branch: :name', ['name' => $student?->branch?->name ?? '—']))
            ->line(__('Please sign in to the student portal or contact the office to arrange payment.'))
            ->salutation(__('Thank you'));
    }
}
