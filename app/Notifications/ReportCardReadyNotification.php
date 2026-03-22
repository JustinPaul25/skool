<?php

namespace App\Notifications;

use App\Models\SchoolYear;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReportCardReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $schoolYearId,
        public int $mediaId,
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
        $schoolYear = SchoolYear::query()->find($this->schoolYearId);
        $media = Media::query()->find($this->mediaId);

        $yearName = $schoolYear?->name ?? 'your program';
        $downloadUrl = $media?->getFullUrl() ?? '';

        return [
            'title' => 'Report card ready',
            'body' => 'Your report card for '.$yearName.' is available to download.',
            'download_url' => $downloadUrl,
            'school_year_id' => $this->schoolYearId,
            'media_id' => $this->mediaId,
        ];
    }
}
