<?php

use App\Models\Branch;
use App\Models\EnrollmentApplication;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('shows the online enrollment wizard when a school year exists', function () {
    SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'is_active' => true,
    ]);

    get(route('enrollment.index'))
        ->assertSuccessful();
});

it('submits an enrollment application and redirects to thank you', function () {
    $branch = Branch::query()->create([
        'name' => 'Main Campus',
        'code' => 'MAIN',
        'address' => '1 Test St',
        'phone' => '555',
        'email' => 'branch@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'is_active' => true,
    ]);

    $grade = GradeLevel::query()->create([
        'name' => 'Grade 1',
        'order' => 1,
        'branch_id' => null,
    ]);

    $response = post(route('enrollment.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Applicant',
        'middle_name' => '',
        'birth_date' => '2018-05-01',
        'gender' => 'female',
        'address' => '123 Street',
        'phone' => '',
        'email' => 'jane.applicant@example.com',
        'guardian_name' => 'Parent Name',
        'guardian_phone' => '555-0100',
        'guardian_relationship' => 'Mother',
        'branch_id' => $branch->id,
        'grade_level_id' => $grade->id,
        'school_year_id' => $schoolYear->id,
        'notes' => 'Test note',
    ]);

    $response->assertRedirect(route('enrollment.thank-you'));
    $response->assertSessionHas('application_id');

    expect(Student::query()->count())->toBe(1)
        ->and(EnrollmentApplication::query()->count())->toBe(1);

    get(route('enrollment.thank-you'))
        ->assertSuccessful();
});

it('rejects enrollment without altcha payload when captcha is enabled', function () {
    $branch = Branch::query()->create([
        'name' => 'Main Campus',
        'code' => 'MAIN',
        'address' => '1 Test St',
        'phone' => '555',
        'email' => 'branch@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'is_active' => true,
    ]);

    $grade = GradeLevel::query()->create([
        'name' => 'Grade 1',
        'order' => 1,
        'branch_id' => null,
    ]);

    config([
        'captcha.altcha.enabled' => true,
        'captcha.altcha.hmac_key' => 'test-hmac-key',
    ]);

    post(route('enrollment.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Applicant',
        'middle_name' => '',
        'birth_date' => '2018-05-01',
        'gender' => 'female',
        'address' => '123 Street',
        'phone' => '',
        'email' => 'jane2@example.com',
        'guardian_name' => 'Parent Name',
        'guardian_phone' => '555-0100',
        'guardian_relationship' => 'Mother',
        'branch_id' => $branch->id,
        'grade_level_id' => $grade->id,
        'school_year_id' => $schoolYear->id,
        'notes' => '',
    ])->assertSessionHasErrors('altcha');
});

it('accepts enrollment when captcha is enabled and ALTCHA verifies', function () {
    $branch = Branch::query()->create([
        'name' => 'Main Campus',
        'code' => 'MAIN',
        'address' => '1 Test St',
        'phone' => '555',
        'email' => 'branch@test.com',
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $schoolYear = SchoolYear::query()->create([
        'name' => '2025-2026',
        'start_date' => '2025-06-01',
        'end_date' => '2026-05-31',
        'is_active' => true,
    ]);

    $grade = GradeLevel::query()->create([
        'name' => 'Grade 1',
        'order' => 1,
        'branch_id' => null,
    ]);

    config([
        'captcha.altcha.enabled' => true,
        'captcha.altcha.hmac_key' => 'test-hmac-key',
    ]);
    $salt = 'testsalt?expires='.(now()->addMinutes(5)->timestamp);
    $number = 123;
    $challenge = hash('sha256', $salt.$number);
    $payload = base64_encode(json_encode([
        'algorithm' => 'SHA-256',
        'challenge' => $challenge,
        'number' => $number,
        'salt' => $salt,
        'signature' => hash_hmac('sha256', $challenge, 'test-hmac-key'),
    ]));

    post(route('enrollment.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Applicant',
        'middle_name' => '',
        'birth_date' => '2018-05-01',
        'gender' => 'female',
        'address' => '123 Street',
        'phone' => '',
        'email' => 'jane3@example.com',
        'guardian_name' => 'Parent Name',
        'guardian_phone' => '555-0100',
        'guardian_relationship' => 'Mother',
        'branch_id' => $branch->id,
        'grade_level_id' => $grade->id,
        'school_year_id' => $schoolYear->id,
        'notes' => '',
        'altcha' => $payload,
    ])->assertRedirect(route('enrollment.thank-you'));
});

it('stores a pending enrollment document upload in session', function () {
    $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

    $response = post(route('enrollment.documents'), [
        'collection' => 'photo',
        'document' => $file,
    ]);

    $response->assertSuccessful()->assertJsonStructure(['id', 'collection', 'original_name']);
});

it('rejects document upload without altcha when captcha is enabled', function () {
    config([
        'captcha.altcha.enabled' => true,
        'captcha.altcha.hmac_key' => 'test-hmac-key',
    ]);

    $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

    post(route('enrollment.documents'), [
        'collection' => 'photo',
        'document' => $file,
    ])->assertSessionHasErrors('altcha');
});
