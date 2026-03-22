<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import PortalLayout from '../../../Layouts/PortalLayout.vue';
import { computed } from 'vue';

defineOptions({
    layout: PortalLayout,
});

type PaymentRow = {
    id: number;
    reference_no: string;
    amount: string;
    type: string;
    paid_at: string | null;
    received_by: string | null | undefined;
};

type Paginator<T> = {
    data: T[];
    prev_page_url: string | null;
    next_page_url: string | null;
};

const page = usePage<{
    balance: string;
    payments: Paginator<PaymentRow>;
    portal: { broadcasting?: boolean } | null;
}>();

const balance = computed(() => page.props.balance);
const payments = computed(() => page.props.payments);
const portal = computed(() => page.props.portal);

const balanceNum = computed(() => Number(balance.value));

const echoHint = computed(() => {
    if (import.meta.env.VITE_ABLY_PUBLIC_KEY) {
        return 'Balance may update automatically when new payments are posted.';
    }
    if (portal.value?.broadcasting) {
        return 'Broadcasting is enabled; ensure VITE_ABLY_PUBLIC_KEY is set in the front-end env for live updates.';
    }
    return 'Live balance updates require broadcasting (e.g. Ably) and VITE_ABLY_PUBLIC_KEY.';
});
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Payments</h1>
            <p class="mt-1 text-sm text-zinc-600">{{ echoHint }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm ring-1 ring-zinc-950/5">
            <h2 class="text-xs font-medium uppercase tracking-wide text-zinc-500">Account balance</h2>
            <p class="mt-2 text-4xl font-semibold tabular-nums text-zinc-900">
                PHP {{ balanceNum.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-zinc-900">Payment history</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-zinc-700">Reference</th>
                            <th class="px-4 py-3 text-right font-semibold text-zinc-700">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-zinc-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-zinc-700">Paid</th>
                            <th class="px-4 py-3 text-left font-semibold text-zinc-700">Received by</th>
                            <th class="px-4 py-3 text-right font-semibold text-zinc-700">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <tr v-for="p in payments.data" :key="p.id">
                            <td class="px-4 py-3 font-mono text-zinc-800">{{ p.reference_no }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium text-zinc-900">
                                {{ Number(p.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                            </td>
                            <td class="px-4 py-3 capitalize text-zinc-600">{{ p.type.replace('_', ' ') }}</td>
                            <td class="px-4 py-3 text-zinc-600">
                                {{ p.paid_at ? new Date(p.paid_at).toLocaleString() : '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600">{{ p.received_by ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a
                                    :href="`/portal/payments/${p.id}/receipt`"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
                                >
                                    Print
                                </a>
                            </td>
                        </tr>
                        <tr v-if="payments.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500">No payments found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <nav
                v-if="payments.prev_page_url || payments.next_page_url"
                class="flex items-center justify-between gap-4 border-t border-zinc-100 px-4 py-3"
                aria-label="Pagination"
            >
                <Link
                    v-if="payments.prev_page_url"
                    :href="payments.prev_page_url"
                    class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
                    preserve-scroll
                >
                    ← Previous
                </Link>
                <span v-else class="text-sm text-zinc-400">← Previous</span>
                <Link
                    v-if="payments.next_page_url"
                    :href="payments.next_page_url"
                    class="text-sm font-medium text-emerald-700 hover:text-emerald-800"
                    preserve-scroll
                >
                    Next →
                </Link>
                <span v-else class="text-sm text-zinc-400">Next →</span>
            </nav>
        </div>
    </div>
</template>
