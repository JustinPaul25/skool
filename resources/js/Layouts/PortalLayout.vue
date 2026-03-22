<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';
import { subscribeStudentPaymentChannel } from '../echo';

type PortalProps = {
    linked?: boolean;
    studentId?: number | null;
    unreadNotifications?: number;
    broadcasting?: boolean;
    balance?: string | null;
};

const page = usePage();

const portal = computed(() => (page.props.portal ?? null) as PortalProps | null);

const userName = computed(() => {
    const u = page.props.auth as { user?: { name: string } } | undefined;
    return u?.user?.name ?? 'Student';
});

const unreadCount = computed(() => portal.value?.unreadNotifications ?? 0);

const flashSuccess = computed(() => {
    const f = page.props.flash as { success?: string } | undefined;
    return f?.success ?? null;
});

const currentPath = computed(() => page.url.split('?')[0] ?? '');

const navItems = [
    { label: 'Dashboard', href: '/portal/dashboard' },
    { label: 'Profile', href: '/portal/profile' },
    { label: 'Grades', href: '/portal/grades' },
    { label: 'Payments', href: '/portal/payments' },
    { label: 'Documents', href: '/portal/documents' },
    { label: 'Notifications', href: '/portal/notifications', badge: true },
];

function linkActive(href: string): boolean {
    if (href === '/portal/dashboard') {
        return currentPath.value === href;
    }

    return currentPath.value === href || currentPath.value.startsWith(`${href}/`);
}

let unsubscribePayments: (() => void) | undefined;

onMounted(() => {
    const sid = portal.value?.studentId;
    const key = import.meta.env.VITE_ABLY_PUBLIC_KEY;
    if (typeof sid === 'number' && key) {
        unsubscribePayments = subscribeStudentPaymentChannel(sid, router);
    }
});

onUnmounted(() => {
    unsubscribePayments?.();
});
</script>

<template>
    <div class="min-h-screen bg-zinc-100 text-zinc-900">
        <div
            v-if="flashSuccess"
            class="border-b border-emerald-200 bg-emerald-50 px-4 py-2 text-center text-sm text-emerald-900"
            role="status"
        >
            {{ flashSuccess }}
        </div>

        <!-- Mobile top bar -->
        <div
            class="relative sticky top-0 z-40 flex items-center justify-between gap-3 border-b border-zinc-200 bg-white px-4 py-3 lg:hidden"
        >
            <div class="flex min-w-0 flex-1 items-center gap-2">
                <span class="truncate text-sm font-semibold text-zinc-800">{{ userName }}</span>
                <Link
                    href="/portal/notifications"
                    class="relative shrink-0 rounded-lg p-1.5 text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900"
                    aria-label="Notifications"
                >
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                        />
                    </svg>
                    <span
                        v-if="unreadCount > 0"
                        class="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white"
                    >
                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                    </span>
                </Link>
            </div>
            <Disclosure v-slot="{ open }" as="div" class="lg:hidden">
                <DisclosureButton
                    class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white p-2 text-zinc-700 shadow-sm hover:bg-zinc-50"
                    :aria-expanded="open"
                    aria-label="Toggle navigation menu"
                >
                    <svg
                        v-if="!open"
                        class="size-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                        />
                    </svg>
                    <svg
                        v-else
                        class="size-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </DisclosureButton>
                <DisclosurePanel
                    class="absolute inset-x-0 top-full z-50 max-h-[min(70vh,calc(100dvh-4rem))] overflow-y-auto border-b border-zinc-200 bg-white shadow-lg"
                >
                    <nav class="flex flex-col gap-1 px-3 py-3" aria-label="Mobile">
                        <Link
                            v-for="item in navItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium"
                            :class="
                                linkActive(item.href)
                                    ? 'bg-emerald-600 text-white'
                                    : 'text-zinc-700 hover:bg-zinc-100'
                            "
                        >
                            <span>{{ item.label }}</span>
                            <span
                                v-if="item.badge && unreadCount > 0"
                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="linkActive(item.href) ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'"
                            >
                                {{ unreadCount }}
                            </span>
                        </Link>
                        <Link
                            href="/portal/logout"
                            method="post"
                            as="button"
                            class="mt-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-red-700 hover:bg-red-50"
                        >
                            Log out
                        </Link>
                    </nav>
                </DisclosurePanel>
            </Disclosure>
        </div>

        <div class="lg:flex lg:min-h-screen">
            <!-- Desktop sidebar -->
            <aside
                class="hidden w-64 shrink-0 flex-col border-r border-zinc-200 bg-white lg:flex"
                aria-label="Sidebar"
            >
                <div class="border-b border-zinc-100 px-5 py-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Student portal</p>
                            <p class="mt-1 truncate text-sm font-semibold text-zinc-900">{{ userName }}</p>
                        </div>
                        <Link
                            href="/portal/notifications"
                            class="relative shrink-0 rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-800"
                            aria-label="Notifications"
                        >
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                                />
                            </svg>
                            <span
                                v-if="unreadCount > 0"
                                class="absolute right-1 top-1 flex size-4 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white"
                            >
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </Link>
                    </div>
                </div>
                <nav class="flex flex-1 flex-col gap-1 p-3" aria-label="Main">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        :class="
                            linkActive(item.href)
                                ? 'bg-emerald-600 text-white'
                                : 'text-zinc-700 hover:bg-zinc-100'
                        "
                    >
                        <span>{{ item.label }}</span>
                        <span
                            v-if="item.badge && unreadCount > 0"
                            class="rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="linkActive(item.href) ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'"
                        >
                            {{ unreadCount }}
                        </span>
                    </Link>
                </nav>
                <div class="border-t border-zinc-100 p-3">
                    <Link
                        href="/portal/logout"
                        method="post"
                        as="button"
                        class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-red-700 hover:bg-red-50"
                    >
                        Log out
                    </Link>
                </div>
            </aside>

            <!-- Main content -->
            <main class="min-h-0 flex-1">
                <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
