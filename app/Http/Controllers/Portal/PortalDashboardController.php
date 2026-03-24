<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\ResolvesPortalStudent;
use App\Models\Grade;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalDashboardController extends Controller
{
    use ResolvesPortalStudent;

    public function index(Request $request): Response
    {
        $student = $this->portalStudent($request);
        $enrollment = $this->activeEnrollment($student);

        $latestPayment = Payment::query()
            ->whereHas('account', fn ($q) => $q->where('student_id', $student->id))
            ->with(['receiver'])
            ->latest('paid_at')
            ->first();

        $gradeSummary = null;
        if ($enrollment) {
            $gradeRows = Grade::query()
                ->where('enrollment_id', $enrollment->id)
                ->whereNotNull('score')
                ->get();

            $gradeSummary = [
                'grades_recorded' => $gradeRows->count(),
                'subjects_with_grades' => $gradeRows->pluck('subject_id')->unique()->count(),
                'average_score' => $gradeRows->count() > 0 ? round((float) $gradeRows->avg('score'), 2) : null,
            ];
        }

        return Inertia::render('Portal/Dashboard', [
            'latestPayment' => $latestPayment ? [
                'reference_no' => $latestPayment->reference_no,
                'amount' => (string) $latestPayment->amount,
                'type' => $latestPayment->type,
                'paid_at' => $latestPayment->paid_at?->toIso8601String(),
            ] : null,
            'gradeSummary' => $gradeSummary,
        ]);
    }
}
