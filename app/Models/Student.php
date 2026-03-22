<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'student_id',
        'user_id',
        'branch_id',
        'first_name',
        'last_name',
        'middle_name',
        'birth_date',
        'gender',
        'address',
        'phone',
        'email',
        'guardian_name',
        'guardian_phone',
        'guardian_relationship',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['student_id', 'first_name', 'last_name', 'email', 'branch_id'])
            ->logOnlyDirty();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrollmentApplications(): HasMany
    {
        return $this->hasMany(EnrollmentApplication::class);
    }

    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function studentRequirements(): HasMany
    {
        return $this->hasMany(StudentRequirement::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Account::class);
    }

    public function grades(): HasManyThrough
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('media-library.disk_name');

        $this->addMediaCollection('photo')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('report_cards')
            ->useDisk($disk)
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}
