<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SchoolSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'school_name',
        'address',
        'phone',
        'active_school_year_id',
        'email_footer_text',
    ];

    /**
     * Singleton row for school-wide configuration.
     */
    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            [],
            ['school_name' => config('app.name')],
        );
    }

    public function activeSchoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'active_school_year_id');
    }

    public function registerMediaCollections(): void
    {
        $disk = (string) config('media-library.disk_name');

        $this->addMediaCollection('logo')
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']);
    }
}
