<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EnrollmentApplication extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const MEDIA_COLLECTION_PHOTO = 'photo';

    public const MEDIA_COLLECTION_BIRTH_CERTIFICATE = 'birth_certificate';

    public const MEDIA_COLLECTION_ADDITIONAL = 'additional_documents';

    /**
     * @var list<string>
     */
    protected $appends = [
        'applicant_name',
    ];

    protected $fillable = [
        'student_id',
        'school_year_id',
        'grade_level_id',
        'branch_id',
        'status',
        'notes',
        'submitted_at',
        'reviewed_by',
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

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getApplicantNameAttribute(): string
    {
        return $this->student?->full_name ?? 'New applicant';
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('media-library.disk_name');

        $this->addMediaCollection(self::MEDIA_COLLECTION_PHOTO)
            ->useDisk($disk)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile();

        $this->addMediaCollection(self::MEDIA_COLLECTION_BIRTH_CERTIFICATE)
            ->useDisk($disk)
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection(self::MEDIA_COLLECTION_ADDITIONAL)
            ->useDisk($disk);
    }
}
