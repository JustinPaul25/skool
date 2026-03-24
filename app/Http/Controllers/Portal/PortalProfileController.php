<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\ResolvesPortalStudent;
use App\Http\Requests\Portal\UpdatePortalProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalProfileController extends Controller
{
    use ResolvesPortalStudent;

    public function index(Request $request): Response
    {
        $student = $this->portalStudent($request);
        $student->loadMissing(['branch']);

        abort_unless((int) $student->user_id === (int) $request->user()->id, 403);

        return Inertia::render('Portal/Profile', [
            'student' => [
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'full_name' => $student->full_name,
                'birth_date' => $student->birth_date?->format('Y-m-d'),
                'gender' => $student->gender,
                'email' => $student->email,
                'phone' => $student->phone,
                'address' => $student->address,
                'guardian_name' => $student->guardian_name,
                'guardian_phone' => $student->guardian_phone,
                'guardian_relationship' => $student->guardian_relationship,
                'branch' => $student->branch?->name,
                'photo_url' => $student->getFirstMediaUrl('photo') ?: null,
            ],
        ]);
    }

    public function update(UpdatePortalProfileRequest $request): RedirectResponse
    {
        $student = $this->portalStudent($request);

        abort_unless((int) $student->user_id === (int) $request->user()->id, 403);

        $student->update($request->validated());

        return redirect()->route('portal.profile')->with('success', __('Profile updated.'));
    }
}
