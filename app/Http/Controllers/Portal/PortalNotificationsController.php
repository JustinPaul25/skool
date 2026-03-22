<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class PortalNotificationsController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20)
            ->through(function (DatabaseNotification $n) {
                /** @var array<string, mixed> $data */
                $data = $n->data;

                return [
                    'id' => $n->id,
                    'title' => (string) ($data['title'] ?? ''),
                    'body' => (string) ($data['body'] ?? ''),
                    'download_url' => isset($data['download_url']) ? (string) $data['download_url'] : null,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at->toIso8601String(),
                ];
            });

        return Inertia::render('Portal/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable !== null
            && $notification->notifiable->is($request->user()),
            403
        );

        $notification->markAsRead();

        return back();
    }
}
