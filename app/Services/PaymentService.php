<?php

namespace App\Services;

use App\Events\PaymentReceived;
use App\Models\Account;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a payment: updates account balance (reduces amount owed), creates payment row, dispatches events.
     *
     * @param  array{enrollment_id?: int|null, amount: float|string, type: string, notes?: string|null, paid_at?: \DateTimeInterface|string|null}  $data
     */
    public function record(Account $account, array $data, ?User $receivedBy = null): Payment
    {
        $receivedBy ??= auth()->user();

        $payment = DB::transaction(function () use ($account, $data, $receivedBy): Payment {
            $account->refresh();

            $amount = (string) $data['amount'];
            $type = $data['type'];

            // Balance represents amount owed; incoming payment reduces outstanding balance.
            $account->balance = bcsub((string) $account->balance, $amount, 2);
            $account->save();

            $paidAt = isset($data['paid_at'])
                ? \Carbon\Carbon::parse($data['paid_at'])
                : now();

            return Payment::query()->create([
                'account_id' => $account->id,
                'enrollment_id' => $data['enrollment_id'] ?? null,
                'amount' => $amount,
                'type' => $type,
                'reference_no' => $this->generateReceiptNumber(),
                'received_by' => $receivedBy?->id,
                'paid_at' => $paidAt,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        $payment->load(['account.student', 'receiver', 'enrollment']);

        PaymentReceived::dispatch($payment);

        return $payment;
    }

    public function generateReceiptNumber(): string
    {
        $year = now()->year;
        $prefix = sprintf('OR-%s-', $year);

        $last = Payment::query()
            ->where('reference_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $next = 1;

        if ($last && preg_match('/-(\d+)$/', (string) $last, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
