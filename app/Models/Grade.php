<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    public const PERIOD_Q1 = 'q1';

    public const PERIOD_Q2 = 'q2';

    public const PERIOD_Q3 = 'q3';

    public const PERIOD_Q4 = 'q4';

    public const PERIOD_FINAL = 'final';

    /**
     * @return array<string, string>
     */
    public static function periodOptions(): array
    {
        return [
            self::PERIOD_Q1 => 'Quarter 1',
            self::PERIOD_Q2 => 'Quarter 2',
            self::PERIOD_Q3 => 'Quarter 3',
            self::PERIOD_Q4 => 'Quarter 4',
            self::PERIOD_FINAL => 'Final',
        ];
    }

    public static function periodLabel(string $period): string
    {
        return self::periodOptions()[$period] ?? $period;
    }

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'period',
        'score',
        'remarks',
        'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
