<?php

namespace App\Events;

use App\Models\EnrollmentApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public EnrollmentApplication $enrollmentApplication) {}
}
