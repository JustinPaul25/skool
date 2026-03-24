<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\ResolvesPortalStudent;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalPaymentsController extends Controller
{
    use ResolvesPortalStudent;

    public function index(Request $request): Response
    {
        $student = $this->portalStudent($request);
        $account = $student->account;

        $payments = Payment::query()
            ->whereHas('account', fn ($q) => $q->where('student_id', $student->id))
            ->with(['receiver'])
            ->latest('paid_at')
            ->paginate(15)
            ->through(fn (Payment $p) => [
                'id' => $p->id,
                'reference_no' => $p->reference_no,
                'amount' => (string) $p->amount,
                'type' => $p->type,
                'paid_at' => $p->paid_at?->toIso8601String(),
                'received_by' => $p->receiver?->name,
            ]);

        return Inertia::render('Portal/Payments/Index', [
            'balance' => $account ? (string) $account->balance : '0.00',
            'payments' => $payments,
        ]);
    }
}
