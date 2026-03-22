<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Payment $payment) {}

    public function broadcastWhen(): bool
    {
        return (bool) $this->payment->account?->student_id;
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('student.'.$this->payment->account->student_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.received';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->payment->loadMissing(['account']);

        return [
            'payment_id' => $this->payment->id,
            'reference_no' => $this->payment->reference_no,
            'amount' => (string) $this->payment->amount,
            'balance' => (string) ($this->payment->account?->balance ?? '0'),
        ];
    }
}
