<?php

namespace App\Notifications\Concerns;

trait QueuesOnNotificationsChannel
{
    public function queueOnNotificationsChannel(): void
    {
        $this->onQueue(config('queue.notifications_queue', 'notifications'));
    }
}
