<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\ResolvesPortalStudent;
use App\Models\Payment;
use App\Services\PaymentReceiptService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalPaymentReceiptController extends Controller
{
    use ResolvesPortalStudent;

    public function __invoke(Request $request, Payment $payment, PaymentReceiptService $receipts): StreamedResponse
    {
        $student = $this->portalStudent($request);

        abort_unless(
            (int) $payment->account?->student_id === (int) $student->id,
            403
        );

        return $receipts->download($payment);
    }
}
