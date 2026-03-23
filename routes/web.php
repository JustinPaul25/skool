<?php

use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalDocumentsController;
use App\Http\Controllers\Portal\PortalGradesController;
use App\Http\Controllers\Portal\PortalNotificationsController;
use App\Http\Controllers\Portal\PortalPaymentReceiptController;
use App\Http\Controllers\Portal\PortalPaymentsController;
use App\Http\Controllers\Portal\PortalProfileController;
use App\Http\Controllers\Portal\PortalReportCardDownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'appName' => config('app.name'),
    ]);
});

Route::get('/online-enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
Route::post('/online-enrollment', [EnrollmentController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('enrollment.store');
Route::post('/enrollment/documents', [EnrollmentController::class, 'uploadDocuments'])
    ->middleware('throttle:20,1')
    ->name('enrollment.documents');
Route::get('/enrollment/altcha/challenge', [EnrollmentController::class, 'altchaChallenge'])
    ->middleware('throttle:60,1')
    ->name('enrollment.altcha.challenge');
Route::get('/online-enrollment/thank-you', [EnrollmentController::class, 'thankYou'])->name('enrollment.thank-you');

Route::get('/media/{media}/{conversion?}', [MediaFileController::class, 'show'])
    ->where('conversion', '[a-zA-Z0-9_-]+')
    ->name('media.file');

Route::middleware('guest')->group(function () {
    Route::get('/portal/login', [PortalAuthController::class, 'create'])->name('portal.login');
    Route::post('/portal/login', [PortalAuthController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('portal.login.store');
});

Route::middleware(['auth', 'role:student'])->prefix('portal')->name('portal.')->group(function () {
    Route::post('/logout', [PortalAuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', PortalDashboardController::class)->name('dashboard');
    Route::get('/profile', [PortalProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::get('/grades', PortalGradesController::class)->name('grades.index');
    Route::get('/report-card/download', PortalReportCardDownloadController::class)->name('report-card.download');
    Route::get('/payments', PortalPaymentsController::class)->name('payments.index');
    Route::get('/payments/{payment}/receipt', PortalPaymentReceiptController::class)->name('payments.receipt');
    Route::get('/documents', [PortalDocumentsController::class, 'index'])->name('documents.index');
    Route::post('/documents/{requirement}', [PortalDocumentsController::class, 'store'])->name('documents.store');
    Route::get('/notifications', [PortalNotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [PortalNotificationsController::class, 'markRead'])->name('notifications.read');
});
