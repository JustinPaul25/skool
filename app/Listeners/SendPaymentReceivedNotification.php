<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;

class SendPaymentReceivedNotification
{
    /**
     * Queue email + database receipt for the student's linked portal user.
     * Real-time UI updates use the broadcast {@see PaymentReceived} event (e.g. Ably).
     */
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment->loadMissing(['account.student.user']);
        $user = $payment->account?->student?->user;

        if (! $user instanceof User) {
            return;
        }

        $user->notify(new PaymentReceivedNotification($payment));
    }
}
