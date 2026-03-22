import Echo from 'laravel-echo';
import type { Router } from '@inertiajs/vue3';
import Pusher from 'pusher-js';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
let echoClient: any = null;

/**
 * Laravel + Ably (Pusher protocol) — set VITE_ABLY_PUBLIC_KEY and optional VITE_ABLY_HOST.
 * @see https://laravel.com/docs/broadcasting#client-ably
 */
export function subscribeStudentPaymentChannel(studentId: number, router: Router): () => void {
    const key = import.meta.env.VITE_ABLY_PUBLIC_KEY;
    if (!key) {
        return () => {};
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (window as any).Pusher = Pusher;

    if (!echoClient) {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call
        echoClient = new Echo({
            broadcaster: 'pusher',
            key: String(key),
            wsHost: import.meta.env.VITE_ABLY_HOST ?? 'realtime-pusher.ably.io',
            wsPort: 443,
            wssPort: 443,
            forceTLS: true,
            encrypted: true,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: `${window.location.origin}/broadcasting/auth`,
            auth: {
                headers: {
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                    Accept: 'application/json',
                },
                withCredentials: true,
            },
        });
    }

    const channelName = `student.${studentId}`;

    const handler = (): void => {
        router.reload({
            preserveScroll: true,
            preserveState: true,
        });
    };

    echoClient.private(channelName).listen('.payment.received', handler);

    return () => {
        echoClient?.leave(channelName);
    };
}
