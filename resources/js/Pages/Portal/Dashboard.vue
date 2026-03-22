<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import { computed } from 'vue';

defineOptions({
    layout: PortalLayout,
});

type Portal = {
    linked?: boolean;
    balance?: string | null;
    activeEnrollment?: {
        status: string;
        school_year: { name: string };
        grade_level?: string | null;
        section?: string | null;
        branch?: string | null;
    } | null;
};

type LatestPayment = {
    reference_no: string;
    amount: string;
    type: string;
    paid_at: string | null;
} | null;

type GradeSummary = {
    grades_recorded: number;
    subjects_with_grades: number;
    average_score: number | null;
} | null;

const page = usePage<{
    portal: Portal | null;
    latestPayment: LatestPayment;
    gradeSummary: GradeSummary;
}>();

const portal = computed(() => page.props.portal);
const latestPayment = computed(() => page.props.latestPayment);
const gradeSummary = computed(() => page.props.gradeSummary);

const balanceDisplay = computed(() => {
    const b = portal.value?.balance;
    return b !== undefined && b !== null ? b : '0.00';
});
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Dashboard</h1>
            <p class="mt-1 text-sm text-zinc-600">Overview of your enrollment, balance, and recent activity.</p>
        </div>

        <div
            v-if="portal && !portal.linked"
            class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
            role="alert"
        >
            Your account does not have a student profile linked yet. Please contact the school office.
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Balance -->
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-zinc-950/5">
                <h2 class="text-xs font-medium uppercase tracking-wide text-zinc-500">Outstanding balance</h2>
                <p class="mt-2 text-3xl font-semibold tabular-nums text-zinc-900">
                    PHP {{ Number(balanceDisplay).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                </p>
                <Link
                    href="/portal/payments"
                    class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:text-emerald-800"
                >
                    View payments →
                </Link>
            </div>

            <!-- Enrollment -->
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-zinc-950/5 sm:col-span-2 lg:col-span-1">
                <h2 class="text-xs font-medium uppercase tracking-wide text-zinc-500">Active enrollment</h2>
                <template v-if="portal?.activeEnrollment">
                    <p class="mt-2 text-lg font-semibold text-zinc-900">
                        {{ portal.activeEnrollment.school_year.name }}
                    </p>
                    <ul class="mt-2 space-y-1 text-sm text-zinc-600">
                        <li v-if="portal.activeEnrollment.grade_level">
                            Grade: {{ portal.activeEnrollment.grade_level }}
                        </li>
                        <li v-if="portal.activeEnrollment.section">Section: {{ portal.activeEnrollment.section }}</li>
                        <li v-if="portal.activeEnrollment.branch">Branch: {{ portal.activeEnrollment.branch }}</li>
                        <li class="capitalize">Status: {{ portal.activeEnrollment.status.replace('_', ' ') }}</li>
                    </ul>
                </template>
                <p v-else class="mt-2 text-sm text-zinc-500">No enrollment for the active school year.</p>
            </div>

            <!-- Latest payment -->
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-zinc-950/5">
                <h2 class="text-xs font-medium uppercase tracking-wide text-zinc-500">Latest payment</h2>
                <template v-if="latestPayment">
                    <p class="mt-2 font-mono text-sm text-zinc-800">{{ latestPayment.reference_no }}</p>
                    <p class="mt-1 text-lg font-semibold text-zinc-900">
                        PHP
                        {{ Number(latestPayment.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                    </p>
                    <p class="mt-1 text-xs capitalize text-zinc-500">{{ latestPayment.type.replace('_', ' ') }}</p>
                    <p v-if="latestPayment.paid_at" class="mt-1 text-xs text-zinc-500">
                        {{ new Date(latestPayment.paid_at).toLocaleString() }}
                    </p>
                </template>
                <p v-else class="mt-2 text-sm text-zinc-500">No payments yet.</p>
                <Link
                    href="/portal/payments"
                    class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:text-emerald-800"
                >
                    All payments →
                </Link>
            </div>

            <!-- Grade summary -->
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm ring-1 ring-zinc-950/5 sm:col-span-2">
                <h2 class="text-xs font-medium uppercase tracking-wide text-zinc-500">Grades (active year)</h2>
                <template v-if="gradeSummary && gradeSummary.grades_recorded > 0">
                    <div class="mt-3 flex flex-wrap gap-6">
                        <div>
                            <p class="text-2xl font-semibold text-zinc-900">{{ gradeSummary.subjects_with_grades }}</p>
                            <p class="text-xs text-zinc-500">Subjects</p>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-zinc-900">{{ gradeSummary.grades_recorded }}</p>
                            <p class="text-xs text-zinc-500">Entries</p>
                        </div>
                        <div v-if="gradeSummary.average_score !== null">
                            <p class="text-2xl font-semibold text-zinc-900">{{ gradeSummary.average_score }}</p>
                            <p class="text-xs text-zinc-500">Avg score</p>
                        </div>
                    </div>
                </template>
                <p v-else class="mt-2 text-sm text-zinc-500">No grades recorded for the active school year.</p>
                <Link
                    href="/portal/grades"
                    class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:text-emerald-800"
                >
                    View grades →
                </Link>
            </div>
        </div>
    </div>
</template>
