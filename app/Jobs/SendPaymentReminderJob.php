<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Account;
use App\Models\SchoolYear;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendPaymentReminderJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $schoolYear = SchoolYear::appCurrent();
        if (! $schoolYear) {
            return;
        }

        $overdueAccounts = Account::query()
            ->where('balance', '>', 0)
            ->whereHas('student.enrollments', function ($q) use ($schoolYear): void {
                $q->where('school_year_id', $schoolYear->id)
                    ->where('status', 'enrolled');
            })
            ->with(['student.user'])
            ->get();

        foreach ($overdueAccounts as $account) {
            if (! $account instanceof Account) {
                continue;
            }

            $student = $account->student;
            if (! $student) {
                continue;
            }

            $recipient = $student->user;

            if ($recipient) {
                $recipient->notify(new PaymentReminderNotification($account));

                continue;
            }

            if ($student->email) {
                Notification::route('mail', $student->email)
                    ->notify(new PaymentReminderNotification($account));
            } else {
                Log::info('SendPaymentReminderJob skipped (no user/email).', [
                    'account_id' => $account->id,
                ]);
            }
        }
    }
}
