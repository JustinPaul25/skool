<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GradeImportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        public int $imported,
        public int $skipped,
        public array $errors = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Grade import completed',
            'body' => "Imported {$this->imported} new grade(s); updated {$this->skipped} row(s).".
                (count($this->errors) > 0 ? ' '.count($this->errors).' row(s) had issues.' : ''),
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
