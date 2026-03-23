<?php

namespace App\Http\Controllers;

use App\Events\EnrollmentSubmitted;
use App\Http\Requests\StoreEnrollmentApplicationRequest;
use App\Http\Requests\UploadEnrollmentDocumentRequest;
use App\Models\Branch;
use App\Models\EnrollmentApplication;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    public function index(): Response
    {
        $schoolYear = SchoolYear::appCurrent()
            ?? SchoolYear::query()->orderByDesc('start_date')->first();

        if (! $schoolYear) {
            return Inertia::render('Enrollment/Index', [
                'branches' => [],
                'gradeLevels' => [],
                'schoolYear' => null,
                'unavailable' => true,
                'captchaEnabled' => false,
                'altchaChallengeUrl' => null,
            ]);
        }

        return Inertia::render('Enrollment/Index', [
            'branches' => Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'gradeLevels' => GradeLevel::query()
                ->orderBy('order')
                ->get(['id', 'name', 'order', 'branch_id']),
            'schoolYear' => [
                'id' => $schoolYear->id,
                'name' => $schoolYear->name,
                'start_date' => $schoolYear->start_date?->format('Y-m-d'),
                'end_date' => $schoolYear->end_date?->format('Y-m-d'),
            ],
            'unavailable' => false,
            'captchaEnabled' => (bool) config('captcha.altcha.enabled'),
            'altchaChallengeUrl' => config('captcha.altcha.enabled')
                ? route('enrollment.altcha.challenge')
                : null,
        ]);
    }

    public function store(StoreEnrollmentApplicationRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('altcha')->all();

        $application = DB::transaction(function () use ($data): EnrollmentApplication {
            $student = Student::query()->create([
                'student_id' => 'STU-'.now()->format('Y').'-'.strtoupper(Str::random(8)),
                'branch_id' => $data['branch_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'guardian_name' => $data['guardian_name'],
                'guardian_phone' => $data['guardian_phone'],
                'guardian_relationship' => $data['guardian_relationship'] ?? null,
            ]);

            $application = EnrollmentApplication::query()->create([
                'student_id' => $student->id,
                'school_year_id' => $data['school_year_id'],
                'grade_level_id' => $data['grade_level_id'],
                'branch_id' => $data['branch_id'],
                'status' => 'submitted',
                'notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            $this->attachPendingUploadsToApplication($application);

            return $application;
        });

        EnrollmentSubmitted::dispatch($application);

        return redirect()
            ->route('enrollment.thank-you')
            ->with('application_id', $application->id);
    }

    public function uploadDocuments(UploadEnrollmentDocumentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $file = $request->file('document');
        $sessionId = session()->getId();
        $directory = 'enrollment-temp/'.$sessionId;

        $uploadsDisk = config('filesystems.uploads_disk', 'spaces');
        $path = $file->store($directory, $uploadsDisk);

        $id = (string) Str::uuid();

        $pending = session()->get('enrollment_pending_uploads', []);
        $singleCollections = [
            EnrollmentApplication::MEDIA_COLLECTION_PHOTO,
            EnrollmentApplication::MEDIA_COLLECTION_BIRTH_CERTIFICATE,
        ];
        if (in_array($validated['collection'], $singleCollections, true)) {
            $pending = array_values(array_filter(
                $pending,
                fn (array $p): bool => ($p['collection'] ?? '') !== $validated['collection'],
            ));
        }
        $pending[] = [
            'id' => $id,
            'path' => $path,
            'collection' => $validated['collection'],
            'original_name' => $file->getClientOriginalName(),
        ];
        session()->put('enrollment_pending_uploads', $pending);

        return response()->json([
            'id' => $id,
            'collection' => $validated['collection'],
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    public function altchaChallenge(): JsonResponse
    {
        if (! config('captcha.altcha.enabled')) {
            abort(404);
        }

        $maxNumber = max((int) config('captcha.altcha.max_number', 100000), 1000);
        $expires = now()->addSeconds((int) config('captcha.altcha.expires_seconds', 300))->timestamp;
        $salt = Str::random(16).'?expires='.$expires;
        $number = random_int(0, $maxNumber);
        $challenge = hash('sha256', $salt.$number);

        return response()->json([
            'algorithm' => 'SHA-256',
            'challenge' => $challenge,
            'maxnumber' => $maxNumber,
            'salt' => $salt,
            'signature' => hash_hmac('sha256', $challenge, (string) config('captcha.altcha.hmac_key')),
        ]);
    }

    public function thankYou(Request $request): Response|RedirectResponse
    {
        $applicationId = $request->session()->pull('application_id');

        if (! $applicationId) {
            return redirect()->route('enrollment.index');
        }

        return Inertia::render('Enrollment/ThankYou', [
            'applicationId' => (int) $applicationId,
            'reference' => 'ENR-'.str_pad((string) $applicationId, 6, '0', STR_PAD_LEFT),
        ]);
    }

    protected function attachPendingUploadsToApplication(EnrollmentApplication $application): void
    {
        $pending = session()->pull('enrollment_pending_uploads', []);
        $sessionId = session()->getId();
        $prefix = 'enrollment-temp/'.$sessionId.'/';
        $uploadsDisk = config('filesystems.uploads_disk', 'spaces');

        foreach ($pending as $item) {
            $path = $item['path'] ?? null;
            $collection = $item['collection'] ?? null;

            if (! is_string($path) || ! is_string($collection)) {
                continue;
            }

            if (! str_starts_with($path, $prefix)) {
                continue;
            }

            if (! Storage::disk($uploadsDisk)->exists($path)) {
                continue;
            }

            $application
                ->addMediaFromDisk($path, $uploadsDisk)
                ->usingFileName(basename($path))
                ->withCustomProperties(['original_name' => $item['original_name'] ?? null])
                ->toMediaCollection($collection);

            Storage::disk($uploadsDisk)->delete($path);
        }
    }
}
