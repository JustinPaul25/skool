<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\ResolvesPortalStudent;
use App\Models\Grade;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalGradesController extends Controller
{
    use ResolvesPortalStudent;

    public function index(Request $request): Response
    {
        $student = $this->portalStudent($request);
        $enrollment = $this->activeEnrollment($student);
        $activeYear = $this->activeSchoolYear();

        $subjects = [];
        if ($enrollment) {
            $grades = Grade::query()
                ->where('enrollment_id', $enrollment->id)
                ->with('subject')
                ->orderBy('subject_id')
                ->get();

            foreach ($grades->groupBy('subject_id') as $rows) {
                $first = $rows->first();
                if (! $first?->subject) {
                    continue;
                }

                $scores = [];
                foreach ([
                    Grade::PERIOD_Q1,
                    Grade::PERIOD_Q2,
                    Grade::PERIOD_Q3,
                    Grade::PERIOD_Q4,
                    Grade::PERIOD_FINAL,
                ] as $period) {
                    $g = $rows->firstWhere('period', $period);
                    $scores[$period] = $g?->score !== null ? (string) $g->score : null;
                }

                $subjects[] = [
                    'id' => $first->subject->id,
                    'name' => $first->subject->name,
                    'code' => $first->subject->code,
                    'scores' => $scores,
                ];
            }
        }

        return Inertia::render('Portal/Grades/Index', [
            'activeSchoolYear' => $activeYear ? [
                'id' => $activeYear->id,
                'name' => $activeYear->name,
            ] : null,
            'hasEnrollment' => $enrollment !== null,
            'periods' => [
                Grade::PERIOD_Q1,
                Grade::PERIOD_Q2,
                Grade::PERIOD_Q3,
                Grade::PERIOD_Q4,
                Grade::PERIOD_FINAL,
            ],
            'periodLabels' => Grade::periodOptions(),
            'subjects' => $subjects,
        ]);
    }
}
