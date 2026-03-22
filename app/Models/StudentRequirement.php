<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StudentRequirement extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'student_id',
        'requirement_id',
        'enrollment_id',
        'status',
        'notes',
        'submitted_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('media-library.disk_name');

        $this->addMediaCollection('submission')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']);
    }
}
