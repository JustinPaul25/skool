<?php

namespace App\Jobs;

use App\Models\Grade;
use App\Models\User;
use App\Notifications\GradeImportCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GradeImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public string $relativePath,
    ) {}

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if (! $user) {
            return;
        }

        $diskName = (string) config('filesystems.uploads_disk', 'spaces');
        $disk = Storage::disk($diskName);

        if (! $disk->exists($this->relativePath)) {
            $user->notify(new GradeImportCompletedNotification(
                imported: 0,
                skipped: 0,
                errors: ['File not found or already processed.'],
            ));

            return;
        }

        $contents = $disk->get($this->relativePath);
        $handle = fopen('php://memory', 'r+b');
        if ($handle === false) {
            $user->notify(new GradeImportCompletedNotification(
                imported: 0,
                skipped: 0,
                errors: ['Could not read CSV file.'],
            ));

            return;
        }
        fwrite($handle, $contents);
        rewind($handle);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $headerLine = fgets($handle);
        if ($headerLine === false) {
            fclose($handle);
            $disk->delete($this->relativePath);
            $user->notify(new GradeImportCompletedNotification(0, 0, ['CSV file is empty.']));

            return;
        }

        $headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
        $header = str_getcsv((string) $headerLine);
        $header = array_map(fn (string $h): string => strtolower(trim($h)), $header);

        $expected = ['enrollment_id', 'subject_id', 'period', 'score', 'remarks'];
        if ($header !== $expected) {
            fclose($handle);
            $disk->delete($this->relativePath);
            $user->notify(new GradeImportCompletedNotification(
                0,
                0,
                ['Invalid header. Expected: '.implode(',', $expected)],
            ));

            return;
        }

        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $data = [
                'enrollment_id' => $row[0] ?? null,
                'subject_id' => $row[1] ?? null,
                'period' => isset($row[2]) ? strtolower(trim((string) $row[2])) : null,
                'score' => $row[3] ?? null,
                'remarks' => $row[4] ?? null,
            ];

            $validator = Validator::make($data, [
                'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
                'subject_id' => ['required', 'integer', 'exists:subjects,id'],
                'period' => ['required', 'in:q1,q2,q3,q4,final'],
                'score' => ['required', 'numeric', 'min:0', 'max:100'],
                'remarks' => ['nullable', 'string', 'max:65535'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Line {$lineNumber}: ".$validator->errors()->first();

                continue;
            }

            $validated = $validator->validated();

            $grade = Grade::query()->updateOrCreate(
                [
                    'enrollment_id' => (int) $validated['enrollment_id'],
                    'subject_id' => (int) $validated['subject_id'],
                    'period' => $validated['period'],
                ],
                [
                    'score' => $validated['score'],
                    'remarks' => $validated['remarks'] ?? null,
                    'graded_by' => $this->userId,
                ],
            );

            if ($grade->wasRecentlyCreated) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        fclose($handle);
        $disk->delete($this->relativePath);

        $user->notify(new GradeImportCompletedNotification(
            imported: $imported,
            skipped: $skipped,
            errors: $errors,
        ));
    }

    /**
     * @param  array<int, string|null>|false  $row
     */
    private function rowIsEmpty(array|false $row): bool
    {
        if ($row === false) {
            return true;
        }

        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
