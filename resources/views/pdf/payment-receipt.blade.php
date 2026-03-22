<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Official Receipt — {{ $payment->reference_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 24px; }
        .title { font-size: 18px; font-weight: bold; }
        .muted { color: #555; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #ddd; }
        th { width: 35%; background: #f5f5f5; }
        .amount { font-size: 16px; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 10px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name', 'School') }}</div>
        <div class="muted">Official Receipt (OR)</div>
    </div>

    <table>
        <tr>
            <th>Reference no.</th>
            <td>{{ $payment->reference_no }}</td>
        </tr>
        <tr>
            <th>Date paid</th>
            <td>{{ $payment->paid_at?->format('M d, Y g:i A') ?? '—' }}</td>
        </tr>
        <tr>
            <th>Student</th>
            <td>{{ $payment->account?->student?->full_name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Student ID</th>
            <td>{{ $payment->account?->student?->student_id ?? '—' }}</td>
        </tr>
        <tr>
            <th>Type</th>
            <td>{{ str($payment->type)->headline() }}</td>
        </tr>
        <tr>
            <th>Amount</th>
            <td class="amount">PHP {{ number_format((float) $payment->amount, 2) }}</td>
        </tr>
        @if($payment->notes)
            <tr>
                <th>Notes</th>
                <td>{{ $payment->notes }}</td>
            </tr>
        @endif
        <tr>
            <th>Received by</th>
            <td>{{ $payment->receiver?->name ?? '—' }}</td>
        </tr>
    </table>

    <div class="footer">
        This document was generated electronically. For verification, quote the reference number above.
    </div>
</body>
</html>
