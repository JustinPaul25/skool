<?php

use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    config([
        'media-library.disk_name' => 'local',
        'media-library.use_same_origin_urls' => true,
    ]);
});

it('forbids unauthenticated requests without a valid signature', function () {
    $branch = Branch::query()->create([
        'name' => 'Test Branch',
        'code' => 'TB-'.uniqid(),
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-'.uniqid(),
        'branch_id' => $branch->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'birth_date' => '2010-01-01',
        'gender' => 'male',
        'guardian_name' => 'Guardian',
        'guardian_phone' => '555-0000',
    ]);

    $media = $student->addMedia(UploadedFile::fake()->image('photo.jpg', 100, 100))
        ->toMediaCollection('photo');

    $this->get(route('media.file', ['media' => $media->id]))
        ->assertForbidden();
});

it('streams media for authenticated users', function () {
    $branch = Branch::query()->create([
        'name' => 'Test Branch',
        'code' => 'TB-'.uniqid(),
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-'.uniqid(),
        'branch_id' => $branch->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'birth_date' => '2010-01-01',
        'gender' => 'male',
        'guardian_name' => 'Guardian',
        'guardian_phone' => '555-0000',
    ]);

    $media = $student->addMedia(UploadedFile::fake()->image('photo.jpg', 100, 100))
        ->toMediaCollection('photo');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('media.file', ['media' => $media->id]))
        ->assertSuccessful();
});

it('uses same-origin paths in generated media urls when enabled', function () {
    $branch = Branch::query()->create([
        'name' => 'Test Branch',
        'code' => 'TB-'.uniqid(),
        'is_active' => true,
        'commission_rate' => 0,
    ]);

    $student = Student::query()->create([
        'student_id' => 'STU-'.uniqid(),
        'branch_id' => $branch->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'birth_date' => '2010-01-01',
        'gender' => 'male',
        'guardian_name' => 'Guardian',
        'guardian_phone' => '555-0000',
    ]);

    $media = $student->addMedia(UploadedFile::fake()->image('photo.jpg', 100, 100))
        ->toMediaCollection('photo');

    expect($media->getUrl())->toStartWith('/media/'.$media->id);
});
