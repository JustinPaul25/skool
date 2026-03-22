<?php

namespace App\Events;

use App\Models\EnrollmentApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public EnrollmentApplication $enrollmentApplication) {}
}
