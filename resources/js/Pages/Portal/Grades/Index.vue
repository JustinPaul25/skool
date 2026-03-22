<script setup lang="ts">
import { Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue';
import PortalLayout from '../../../Layouts/PortalLayout.vue';
import { computed } from 'vue';

defineOptions({
    layout: PortalLayout,
});

type SubjectRow = {
    id: number;
    name: string;
    code: string;
    scores: Record<string, string | null>;
};

const props = defineProps<{
    activeSchoolYear: { id: number; name: string } | null;
    hasEnrollment: boolean;
    periods: string[];
    periodLabels: Record<string, string>;
    subjects: SubjectRow[];
}>();

const downloadHref = computed(() => {
    if (!props.activeSchoolYear) {
        return '/portal/report-card/download';
    }
    return `/portal/report-card/download?school_year_id=${props.activeSchoolYear.id}`;
});
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Grades</h1>
                <p v-if="activeSchoolYear" class="mt-1 text-sm text-zinc-600">
                    School year: <span class="font-medium text-zinc-800">{{ activeSchoolYear.name }}</span>
                </p>
                <p v-else class="mt-1 text-sm text-amber-700">No active school year is configured.</p>
            </div>
            <a
                :href="downloadHref"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700"
            >
                Download report card
            </a>
        </div>

        <div
            v-if="!hasEnrollment"
            class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
            role="status"
        >
            You are not enrolled for the active school year, so no grades are shown.
        </div>

        <template v-else>
            <!-- Desktop: full table -->
            <div class="hidden overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-sm md:block">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-zinc-700">Subject</th>
                            <th class="px-4 py-3 text-left font-semibold text-zinc-700">Code</th>
                            <th
                                v-for="p in periods"
                                :key="p"
                                class="px-4 py-3 text-center font-semibold text-zinc-700"
                            >
                                {{ periodLabels[p] ?? p }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <tr v-for="sub in subjects" :key="sub.id">
                            <td class="px-4 py-3 font-medium text-zinc-900">{{ sub.name }}</td>
                            <td class="px-4 py-3 text-zinc-600">{{ sub.code }}</td>
                            <td
                                v-for="p in periods"
                                :key="p"
                                class="px-4 py-3 text-center tabular-nums text-zinc-800"
                            >
                                {{ sub.scores[p] ?? '—' }}
                            </td>
                        </tr>
                        <tr v-if="subjects.length === 0">
                            <td :colspan="2 + periods.length" class="px-4 py-8 text-center text-zinc-500">
                                No grades recorded yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile: period tabs -->
            <div class="md:hidden">
                <TabGroup>
                    <TabList class="flex gap-1 overflow-x-auto rounded-lg bg-zinc-200/80 p-1">
                        <Tab
                            v-for="p in periods"
                            :key="p"
                            v-slot="{ selected }"
                            as="template"
                        >
                            <button
                                type="button"
                                class="shrink-0 rounded-md px-3 py-2 text-xs font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                :class="
                                    selected
                                        ? 'bg-white text-emerald-800 shadow-sm'
                                        : 'text-zinc-600 hover:text-zinc-900'
                                "
                            >
                                {{ (periodLabels[p] ?? p).replace('Quarter ', 'Q') }}
                            </button>
                        </Tab>
                    </TabList>
                    <TabPanels class="mt-4">
                        <TabPanel
                            v-for="p in periods"
                            :key="p"
                            class="space-y-2 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm focus:outline-none"
                        >
                            <p class="text-xs font-medium text-zinc-500">{{ periodLabels[p] ?? p }}</p>
                            <ul class="divide-y divide-zinc-100">
                                <li
                                    v-for="sub in subjects"
                                    :key="sub.id"
                                    class="flex items-center justify-between py-2 text-sm"
                                >
                                    <span class="font-medium text-zinc-900">{{ sub.name }}</span>
                                    <span class="tabular-nums text-zinc-700">{{ sub.scores[p] ?? '—' }}</span>
                                </li>
                            </ul>
                            <p v-if="subjects.length === 0" class="py-4 text-center text-sm text-zinc-500">
                                No grades yet.
                            </p>
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </template>
    </div>
</template>
