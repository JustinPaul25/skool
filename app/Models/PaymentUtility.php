<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentUtility extends Model
{
    public const TYPE_TUITION = 'tuition';

    public const TYPE_MISCELLANEOUS = 'miscellaneous';

    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_PENALTY = 'penalty';

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_TUITION => 'Tuition',
            self::TYPE_MISCELLANEOUS => 'Miscellaneous',
            self::TYPE_DISCOUNT => 'Discount',
            self::TYPE_PENALTY => 'Penalty',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return self::typeOptions()[$type] ?? Str::headline($type);
    }

    protected $fillable = [
        'name',
        'amount',
        'type',
        'grade_level_id',
        'branch_id',
        'school_year_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
