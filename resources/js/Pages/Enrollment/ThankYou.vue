<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    applicationId: number;
    reference: string;
}>();
</script>

<template>
    <Head title="Application submitted" />

    <div class="min-h-screen bg-gradient-to-b from-amber-50 to-white px-4 py-16">
        <div class="mx-auto max-w-lg rounded-2xl border border-amber-100 bg-white p-8 shadow-lg">
            <div class="mb-6 flex justify-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <h1 class="text-center text-2xl font-bold text-gray-900">Application received</h1>
            <p class="mt-2 text-center text-gray-600">
                Thank you for submitting your enrollment application.
            </p>
            <div class="mt-8 rounded-xl bg-amber-50 px-4 py-3 text-center">
                <p class="text-sm font-medium text-amber-800">Your reference number</p>
                <p class="mt-1 font-mono text-2xl font-bold tracking-wide text-amber-900">{{ reference }}</p>
                <p class="mt-1 text-xs text-amber-700">Application ID #{{ applicationId }}</p>
            </div>
            <p class="mt-6 text-center text-sm text-gray-500">
                We will contact you if we need more information. A confirmation email has been sent if you provided an email address.
            </p>
            <div class="mt-8 flex justify-center">
                <Link
                    href="/"
                    class="rounded-lg bg-amber-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-amber-700"
                >
                    Back to home
                </Link>
            </div>
        </div>
    </div>
</template>
