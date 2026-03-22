<?php

namespace App\Exports;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected ?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    /**
     * @return Builder<Payment>
     */
    public function query(): Builder
    {
        $query = Payment::query()
            ->with(['account.student', 'receiver']);

        if ($this->user && $this->user->hasRole('branch_manager') && $this->user->branch_id) {
            $query->whereHas('account.student', fn (Builder $q) => $q->where('branch_id', $this->user->branch_id));
        }

        return $query->orderByDesc('paid_at');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'reference_no',
            'student',
            'amount',
            'type',
            'paid_at',
            'received_by',
        ];
    }

    /**
     * @return array<int, string|null>
     */
    public function map(mixed $payment): array
    {
        /** @var Payment $payment */
        return [
            $payment->reference_no,
            $payment->account?->student?->full_name,
            (string) $payment->amount,
            $payment->type,
            $payment->paid_at?->toDateTimeString(),
            $payment->receiver?->name,
        ];
    }
}
