<?php

use App\Models\Account;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\PaymentService;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(fn () => $this->seed(RoleSeeder::class));

it('records a payment and reduces account balance', function () {
    Event::fake();

    $branch = Branch::query()->create([
        'name' => 'North',
        'code' => 'N',
        'address' => 'x',
        'phone' => '1',
        'email' => 'n@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-PAY-1',
        'branch_id' => $branch->id,
        'first_name' => 'Pay',
        'last_name' => 'Student',
        'middle_name' => null,
        'birth_date' => '2015-01-01',
        'gender' => 'male',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
        'email' => null,
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

    expect($payment->reference_no)->toStartWith('OR-'.now()->year)
        ->and(Payment::query()->count())->toBe(1);

    $account->refresh();
    expect((string) $account->balance)->toBe('750.00');
});

it('allows staff to view payments policy but not update', function () {
    $branch = Branch::query()->create([
        'name' => 'South',
        'code' => 'S',
        'address' => 'x',
        'phone' => '1',
        'email' => 's@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-PAY-2',
        'branch_id' => $branch->id,
        'first_name' => 'A',
        'last_name' => 'B',
        'middle_name' => null,
        'birth_date' => '2015-01-01',
        'gender' => 'female',
        'guardian_name' => 'G',
        'guardian_phone' => '1',
        'email' => null,
    ]);

    $account = Account::query()->create([
        'student_id' => $student->id,
        'balance' => '100.00',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('administrator');

    $payment = app(PaymentService::class)->record($account, [
        'amount' => '10.00',
        'type' => 'tuition',
        'paid_at' => now(),
    ], $admin);

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    expect($staff->can('viewAny', Payment::class))->toBeTrue()
        ->and($staff->can('view', $payment))->toBeTrue()
        ->and($staff->can('update', $payment))->toBeFalse();
});
