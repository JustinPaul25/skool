<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentReceiptService
{
    public function download(Payment $payment): StreamedResponse
    {
        $payment->loadMissing(['account.student', 'receiver', 'enrollment']);

        activity()
            ->performedOn($payment)
            ->causedBy(auth()->user())
            ->log('Printed official receipt (OR)');

        return Pdf::loadView('pdf.payment-receipt', ['payment' => $payment])
            ->download($payment->reference_no.'.pdf');
    }
}
