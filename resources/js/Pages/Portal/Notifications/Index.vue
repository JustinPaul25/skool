<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import PortalLayout from '../../../Layouts/PortalLayout.vue';
import { computed } from 'vue';

defineOptions({
    layout: PortalLayout,
});

type NotifRow = {
    id: string;
    title: string;
    body: string;
    download_url: string | null;
    read_at: string | null;
    created_at: string;
};

type Paginator<T> = {
    data: T[];
    prev_page_url: string | null;
    next_page_url: string | null;
};

const page = usePage<{ notifications: Paginator<NotifRow> }>();

const notifications = computed(() => page.props.notifications);
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Notifications</h1>
            <p class="mt-1 text-sm text-zinc-600">Messages from the school, including report cards and payment updates.</p>
        </div>

        <ul class="space-y-3">
            <li
                v-for="n in notifications.data"
                :key="n.id"
                class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm"
                :class="n.read_at ? 'opacity-90' : 'ring-1 ring-emerald-500/20'"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900">{{ n.title || 'Notification' }}</p>
                        <p v-if="n.body" class="mt-1 text-sm text-zinc-600">{{ n.body }}</p>
                        <p class="mt-2 text-xs text-zinc-500">{{ new Date(n.created_at).toLocaleString() }}</p>
                        <a
                            v-if="n.download_url"
                            :href="n.download_url"
                            target="_blank"
                            rel="noopener"
                            class="mt-2 inline-block text-sm font-medium text-emerald-700 hover:text-emerald-800"
                        >
                            Open link
                        </a>
                    </div>
                    <div class="shrink-0">
                        <Link
                            v-if="!n.read_at"
                            :href="`/portal/notifications/${n.id}/read`"
                            method="patch"
                            as="button"
                            class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            preserve-scroll
                        >
                            Mark read
                        </Link>
                        <span v-else class="text-xs font-medium text-emerald-700">Read</span>
                    </div>
                </div>
            </li>
            <li v-if="notifications.data.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500">
                No notifications yet.
            </li>
        </ul>

        <nav
            v-if="notifications.prev_page_url || notifications.next_page_url"
            class="flex items-center justify-between gap-4"
            aria-label="Pagination"
        >
            <Link
                v-if="notifications.prev_page_url"
                :href="notifications.prev_page_url"
                class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
                preserve-scroll
            >
                ← Previous
            </Link>
            <span v-else class="text-sm text-zinc-400">← Previous</span>
            <Link
                v-if="notifications.next_page_url"
                :href="notifications.next_page_url"
                class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
                preserve-scroll
            >
                Next →
            </Link>
            <span v-else class="text-sm text-zinc-400">Next →</span>
        </nav>
    </div>
</template>
