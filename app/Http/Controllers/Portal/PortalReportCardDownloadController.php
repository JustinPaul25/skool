<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\ResolvesPortalStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortalReportCardDownloadController extends Controller
{
    use ResolvesPortalStudent;

    public function download(Request $request): RedirectResponse
    {
        $student = $this->portalStudent($request);
        $activeYear = $this->activeSchoolYear();

        $schoolYearId = (int) $request->query('school_year_id', $activeYear?->id ?? 0);
        abort_unless($schoolYearId > 0, 404);

        $mediaItems = $student->getMedia('report_cards');

        $media = $mediaItems->first(function ($m) use ($schoolYearId) {
            return (int) $m->getCustomProperty('school_year_id') === $schoolYearId;
        });

        if ($media === null) {
            $media = $mediaItems->sortByDesc('created_at')->first();
        }

        abort_if($media === null, 404, __('No report card is available yet.'));

        $cloudinary = $media->getCustomProperty('cloudinary_secure_url');
        if (is_string($cloudinary) && $cloudinary !== '') {
            return redirect()->away($cloudinary);
        }

        return redirect()->to($media->getFullUrl());
    }
}
