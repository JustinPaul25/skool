<?php

namespace App\Http\Middleware;

use App\Models\SchoolSetting;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'student_id' => $request->user()->student?->id,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
            ],
            'portal' => $this->portalShare($request),
            'school' => $this->schoolShare(),
        ];
    }

    /**
     * @return array{name: string, logo_url: ?string, address: ?string, phone: ?string}
     */
    private function schoolShare(): array
    {
        $setting = SchoolSetting::query()->first();

        return [
            'name' => $setting?->school_name ?: (string) config('app.name'),
            'logo_url' => $setting?->getFirstMediaUrl('logo') ?: null,
            'address' => $setting?->address,
            'phone' => $setting?->phone,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function portalShare(Request $request): ?array
    {
        if (! $request->is('portal') && ! $request->is('portal/*')) {
            return null;
        }

        if (! $request->user()?->hasRole('student')) {
            return null;
        }

        $user = $request->user();
        $user->loadMissing('student');
        $student = $user->student;

        $unread = $user->unreadNotifications()->count();

        if ($student === null) {
            return [
                'linked' => false,
                'studentId' => null,
                'photoUrl' => null,
                'balance' => null,
                'activeEnrollment' => null,
                'unreadNotifications' => $unread,
                'broadcasting' => config('broadcasting.default') !== 'null',
            ];
        }

        $student->loadMissing(['branch']);
        $activeYear = SchoolYear::appCurrent();

        $enrollment = null;
        if ($activeYear !== null) {
            $enrollment = $student->enrollments()
                ->where('school_year_id', $activeYear->id)
                ->with(['gradeLevel', 'section', 'schoolYear'])
                ->first();
        }

        $account = $student->account;

        return [
            'linked' => true,
            'studentId' => $student->id,
            'photoUrl' => $student->getFirstMediaUrl('photo') ?: null,
            'balance' => $account ? (string) $account->balance : '0.00',
            'activeEnrollment' => $enrollment ? [
                'id' => $enrollment->id,
                'status' => $enrollment->status,
                'school_year' => [
                    'id' => $enrollment->schoolYear->id,
                    'name' => $enrollment->schoolYear->name,
                ],
                'grade_level' => $enrollment->gradeLevel?->name,
                'section' => $enrollment->section?->name,
                'branch' => $student->branch?->name,
            ] : null,
            'unreadNotifications' => $unread,
            'broadcasting' => config('broadcasting.default') !== 'null',
        ];
    }
}
