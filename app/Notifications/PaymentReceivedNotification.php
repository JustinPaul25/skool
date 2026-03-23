<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\Concerns\QueuesOnNotificationsChannel;
use App\Services\PaymentReceiptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesOnNotificationsChannel;

    public function __construct(public Payment $payment)
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
        $payment = $this->payment->loadMissing(['account.student', 'receiver', 'enrollment']);
        $student = $payment->account?->student;

        $message = (new MailMessage)
            ->subject(__('Payment received — :ref', ['ref' => $payment->reference_no]))
            ->greeting(__('Hello!'))
            ->line(__('We recorded a payment for :name.', [
                'name' => $student?->getFullName() ?? __('your account'),
            ]))
            ->line(__('Amount: :amount', ['amount' => number_format((float) $payment->amount, 2)]))
            ->line(__('Reference: :ref', ['ref' => $payment->reference_no]))
            ->line(__('Date: :date', ['date' => $payment->paid_at?->timezone(config('app.timezone'))->toDayDateTimeString() ?? '—']))
            ->line(__('Outstanding balance: :balance', [
                'balance' => number_format((float) ($payment->account?->balance ?? 0), 2),
            ]))
            ->line(__('A PDF receipt is attached.'))
            ->salutation(__('Thank you'));

        $pdf = app(PaymentReceiptService::class)->pdfBinary($payment);

        return $message->attachData($pdf, $payment->reference_no.'.pdf', [
            'mime' => 'application/pdf',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $payment = $this->payment->loadMissing(['account.student']);

        return [
            'title' => __('Payment received'),
            'body' => __('We recorded :ref for :amount.', [
                'ref' => $payment->reference_no,
                'amount' => number_format((float) $payment->amount, 2),
            ]),
            'payment_id' => $payment->id,
            'reference_no' => $payment->reference_no,
            'amount' => (string) $payment->amount,
            'balance' => (string) ($payment->account?->balance ?? '0'),
        ];
    }
}
