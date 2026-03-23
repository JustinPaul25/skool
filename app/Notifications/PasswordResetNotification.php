<?php

namespace App\Notifications;

use App\Notifications\Concerns\QueuesOnNotificationsChannel;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;
    use QueuesOnNotificationsChannel;

    public function __construct(#[\SensitiveParameter] $token)
    {
        parent::__construct($token);
        $this->queueOnNotificationsChannel();
    }

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }
}
