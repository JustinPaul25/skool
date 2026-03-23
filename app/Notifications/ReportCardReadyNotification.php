<?php

namespace App\Notifications;

use App\Models\SchoolYear;
use App\Notifications\Concerns\QueuesOnNotificationsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReportCardReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesOnNotificationsChannel;

    public function __construct(
        public int $schoolYearId,
        public int $mediaId,
    ) {
        $this->queueOnNotificationsChannel();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $schoolYear = SchoolYear::query()->find($this->schoolYearId);
        $media = Media::query()->find($this->mediaId);

        $downloadUrl = $media
            ? URL::temporarySignedRoute(
                'media.file',
                now()->addDays(14),
                ['media' => $media->id],
            )
            : url('/');

        return (new MailMessage)
            ->subject(__('Report card ready'))
            ->greeting(__('Hello!'))
            ->line(__('Your report card for :year is available to download.', [
                'year' => $schoolYear?->name ?? __('your program'),
            ]))
            ->action(__('Download report card'), $downloadUrl)
            ->line(__('This link expires in 14 days. You can also download report cards from the student portal.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $schoolYear = SchoolYear::query()->find($this->schoolYearId);
        $media = Media::query()->find($this->mediaId);

        $downloadUrl = $media
            ? URL::temporarySignedRoute(
                'media.file',
                now()->addDays(14),
                ['media' => $media->id],
            )
            : '';

        return [
            'title' => __('Report card ready'),
            'body' => __('Your report card for :year is available to download.', [
                'year' => $schoolYear?->name ?? __('your program'),
            ]),
            'download_url' => $downloadUrl,
            'school_year_id' => $this->schoolYearId,
            'media_id' => $this->mediaId,
        ];
    }
}
