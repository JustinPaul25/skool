<?php

use App\Events\PaymentReceived;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('records a payment, reduces the balance, dispatches PaymentReceived, and notifies the portal user', function () {
    Notification::fake();

    $branch = Branch::query()->create([
        'name' => 'North',
        'code' => 'N-'.uniqid(),
        'address' => 'x',
        'phone' => '1',
        'email' => 'n@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $portalUser = User::factory()->create();
    $portalUser->assignRole('student');

    $student = Student::query()->create([
        'student_id' => 'STU-PAY-PORTAL-'.uniqid(),
        'branch_id' => $branch->id,
        'user_id' => $portalUser->id,
        'first_name' => 'Portal',
        'last_name' => 'Student',
        'middle_name' => null,
        'birth_date' => '2015-01-01',
        'gender' => 'male',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
        'email' => 'portal@example.com',
    ]);

    $account = Account::query()->create([
        'student_id' => $student->id,
        'balance' => '1000.00',
    ]);

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->actingAs($staff);

    $payment = app(PaymentService::class)->record($account, [
        'amount' => '250.00',
        'type' => 'tuition',
        'notes' => 'Test',
        'paid_at' => now(),
    ]);

    expect($payment)->toBeInstanceOf(Payment::class);

    $account->refresh();
    expect((string) $account->balance)->toBe('750.00');

    Notification::assertSentTo($portalUser, PaymentReceivedNotification::class);
});

it('dispatches PaymentReceived when recording a payment', function () {
    Event::fake([PaymentReceived::class]);

    $branch = Branch::query()->create([
        'name' => 'North',
        'code' => 'N-'.uniqid(),
        'address' => 'x',
        'phone' => '1',
        'email' => 'n@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-PAY-PORTAL-'.uniqid(),
        'branch_id' => $branch->id,
        'first_name' => 'Portal',
        'last_name' => 'Student',
        'middle_name' => null,
        'birth_date' => '2015-01-01',
        'gender' => 'male',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
        'email' => 'portal@example.com',
    ]);

    $account = Account::query()->create([
        'student_id' => $student->id,
        'balance' => '1000.00',
    ]);

    $staff = User::factory()->create();
    $staff->assignRole('staff');
    $this->actingAs($staff);

    app(PaymentService::class)->record($account, [
        'amount' => '250.00',
        'type' => 'tuition',
        'notes' => 'Test',
        'paid_at' => now(),
    ]);

    Event::assertDispatched(PaymentReceived::class);
});
