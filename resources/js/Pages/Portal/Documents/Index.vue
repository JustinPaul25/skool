<script setup lang="ts">
import PortalLayout from '../../../Layouts/PortalLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: PortalLayout,
});

type ReqRow = {
    requirement_id: number;
    student_requirement_id: number | null;
    name: string;
    description: string | null;
    is_required: boolean;
    status: string;
    submitted_at: string | null;
    has_file: boolean;
    file_name: string | null;
};

const props = defineProps<{
    hasEnrollment: boolean;
    requirements: ReqRow[];
}>();

const uploadingId = ref<number | null>(null);

function statusColor(status: string): string {
    return (
        {
            pending: 'bg-amber-100 text-amber-900',
            submitted: 'bg-sky-100 text-sky-900',
            verified: 'bg-emerald-100 text-emerald-900',
            rejected: 'bg-red-100 text-red-900',
        }[status] ?? 'bg-zinc-100 text-zinc-800'
    );
}

function onFileChange(requirementId: number, event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) {
        return;
    }
    uploadingId.value = requirementId;
    router.post(
        `/portal/documents/${requirementId}`,
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => {
                uploadingId.value = null;
                input.value = '';
            },
        },
    );
}
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Documents</h1>
            <p class="mt-1 text-sm text-zinc-600">Upload files for each requirement. Allowed: PDF, JPEG, PNG, WebP (max 10 MB).</p>
        </div>

        <div
            v-if="!hasEnrollment"
            class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
            role="status"
        >
            You need an active enrollment for the current school year to view or submit documents.
        </div>

        <ul v-else class="space-y-4">
            <li
                v-for="req in requirements"
                :key="req.requirement_id"
                class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-semibold text-zinc-900">{{ req.name }}</h2>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="statusColor(req.status)"
                            >
                                {{ req.status }}
                            </span>
                            <span
                                v-if="req.is_required"
                                class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700"
                            >
                                Required
                            </span>
                        </div>
                        <p v-if="req.description" class="mt-2 text-sm text-zinc-600">{{ req.description }}</p>
                        <p v-if="req.submitted_at" class="mt-2 text-xs text-zinc-500">
                            Submitted {{ new Date(req.submitted_at).toLocaleString() }}
                        </p>
                        <p v-if="req.has_file && req.file_name" class="mt-1 text-xs text-zinc-500">
                            File: {{ req.file_name }}
                        </p>
                    </div>
                    <div class="shrink-0">
                        <label
                            class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50"
                            :class="{ 'pointer-events-none opacity-50': uploadingId === req.requirement_id }"
                        >
                            <span v-if="uploadingId === req.requirement_id">Uploading…</span>
                            <span v-else>{{ req.has_file ? 'Replace file' : 'Upload' }}</span>
                            <input
                                type="file"
                                class="sr-only"
                                accept=".pdf,image/jpeg,image/png,image/webp"
                                @change="onFileChange(req.requirement_id, $event)"
                            />
                        </label>
                    </div>
                </div>
            </li>
            <li v-if="requirements.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500">
                No document requirements apply to your grade level.
            </li>
        </ul>
    </div>
</template>
