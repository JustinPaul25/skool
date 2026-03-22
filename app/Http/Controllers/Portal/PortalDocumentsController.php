<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\ResolvesPortalStudent;
use App\Http\Requests\Portal\StorePortalDocumentRequest;
use App\Models\Requirement;
use App\Models\StudentRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalDocumentsController extends Controller
{
    use ResolvesPortalStudent;

    public function index(Request $request): Response
    {
        $student = $this->portalStudent($request);
        $enrollment = $this->activeEnrollment($student);

        if ($enrollment === null) {
            return Inertia::render('Portal/Documents/Index', [
                'hasEnrollment' => false,
                'requirements' => [],
            ]);
        }

        $requirements = Requirement::query()
            ->where(function ($q) use ($enrollment) {
                $q->whereNull('grade_level_id')
                    ->orWhere('grade_level_id', $enrollment->grade_level_id);
            })
            ->orderBy('name')
            ->get();

        $payload = $requirements->map(function (Requirement $req) use ($student, $enrollment) {
            $sr = StudentRequirement::query()
                ->where('student_id', $student->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('requirement_id', $req->id)
                ->first();

            if ($sr !== null) {
                $sr->loadMissing('media');
            }

            return [
                'requirement_id' => $req->id,
                'student_requirement_id' => $sr?->id,
                'name' => $req->name,
                'description' => $req->description,
                'is_required' => $req->is_required,
                'status' => $sr?->status ?? 'pending',
                'submitted_at' => $sr?->submitted_at?->toIso8601String(),
                'has_file' => $sr !== null && $sr->getFirstMedia('submission') !== null,
                'file_name' => $sr?->getFirstMedia('submission')?->file_name,
            ];
        });

        return Inertia::render('Portal/Documents/Index', [
            'hasEnrollment' => true,
            'requirements' => $payload,
        ]);
    }

    public function store(StorePortalDocumentRequest $request, Requirement $requirement): RedirectResponse
    {
        $student = $this->portalStudent($request);
        $enrollment = $this->activeEnrollment($student);

        abort_if($enrollment === null, 404, __('No active enrollment for this school year.'));

        abort_unless($this->requirementAppliesToEnrollment($requirement, $enrollment), 404);

        $studentRequirement = StudentRequirement::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'requirement_id' => $requirement->id,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'status' => 'pending',
            ],
        );

        $studentRequirement->clearMediaCollection('submission');

        $studentRequirement
            ->addMediaFromRequest('file')
            ->toMediaCollection('submission');

        $studentRequirement->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('portal.documents.index')->with('success', __('Document uploaded.'));
    }
}
