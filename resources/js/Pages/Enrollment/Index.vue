<script setup lang="ts">
import { Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import 'altcha';

import { academicSchema, guardianSchema, personalSchema } from '../../lib/enrollmentSchemas';

type Branch = { id: number; name: string; code: string };
type GradeLevel = { id: number; name: string; order: number; branch_id: number | null };
type SchoolYear = { id: number; name: string; start_date: string | null; end_date: string | null };

const props = defineProps<{
    branches: Branch[];
    gradeLevels: GradeLevel[];
    schoolYear: SchoolYear | null;
    unavailable: boolean;
    captchaEnabled?: boolean;
    altchaChallengeUrl?: string | null;
}>();

const captchaEnabled = computed(() => props.captchaEnabled === true && !!props.altchaChallengeUrl);

const COLLECTION_PHOTO = 'photo';
const COLLECTION_BIRTH = 'birth_certificate';
const COLLECTION_EXTRA = 'additional_documents';

const step = ref(0);
const zodFieldErrors = ref<Record<string, string>>({});
const uploadError = ref<string | null>(null);
const captchaError = ref<string | null>(null);
const altchaRef = ref<HTMLElement | null>(null);

type PendingUpload = { id: string; collection: string; original_name: string };
const pendingUploads = ref<PendingUpload[]>([]);

const form = useForm({
    first_name: '',
    last_name: '',
    middle_name: '',
    birth_date: '',
    gender: '' as 'male' | 'female' | '',
    address: '',
    guardian_name: '',
    guardian_phone: '',
    guardian_relationship: '',
    phone: '',
    email: '',
    branch_id: null as number | null,
    grade_level_id: null as number | null,
    school_year_id: props.schoolYear?.id ?? 0,
    notes: '',
    altcha: '',
});

const filteredGradeLevels = computed(() => {
    const bid = form.branch_id;
    if (!bid) {
        return [];
    }

    return props.gradeLevels.filter((g) => g.branch_id === null || g.branch_id === bid);
});

watch(
    () => form.branch_id,
    () => {
        const ok = filteredGradeLevels.value.some((g) => g.id === form.grade_level_id);
        if (!ok) {
            form.grade_level_id = null;
        }
    },
);

const stepLabels = ['Personal', 'Guardian', 'Academic', 'Documents', 'Review'];

function clearZodErrors(): void {
    zodFieldErrors.value = {};
    captchaError.value = null;
}

function applyZodErrors(err: { flatten: () => { fieldErrors: Record<string, string[] | undefined> } }): void {
    const flat = err.flatten().fieldErrors;
    const next: Record<string, string> = {};
    for (const [key, val] of Object.entries(flat)) {
        if (val?.[0]) {
            next[key] = val[0];
        }
    }
    zodFieldErrors.value = next;
}

function validateCurrentStep(): boolean {
    clearZodErrors();
    uploadError.value = null;

    const d = form.data();

    if (step.value === 0) {
        const r = personalSchema.safeParse({
            first_name: d.first_name,
            last_name: d.last_name,
            middle_name: d.middle_name || undefined,
            birth_date: d.birth_date,
            gender: d.gender,
            address: d.address || undefined,
        });
        if (!r.success) {
            applyZodErrors(r.error);

            return false;
        }

        return true;
    }

    if (step.value === 1) {
        const r = guardianSchema.safeParse({
            guardian_name: d.guardian_name,
            guardian_phone: d.guardian_phone,
            guardian_relationship: d.guardian_relationship || undefined,
            phone: d.phone || undefined,
            email: d.email || '',
        });
        if (!r.success) {
            applyZodErrors(r.error);

            return false;
        }

        return true;
    }

    if (step.value === 2) {
        const r = academicSchema.safeParse({
            branch_id: d.branch_id,
            grade_level_id: d.grade_level_id,
            school_year_id: d.school_year_id,
            notes: d.notes || undefined,
        });
        if (!r.success) {
            applyZodErrors(r.error);

            return false;
        }

        return true;
    }

    if (step.value === 3) {
        const hasPhoto = pendingUploads.value.some((u) => u.collection === COLLECTION_PHOTO);
        const hasBirth = pendingUploads.value.some((u) => u.collection === COLLECTION_BIRTH);
        if (!hasPhoto || !hasBirth) {
            uploadError.value = 'Please upload a student photo and a birth certificate (PDF or image).';

            return false;
        }

        return true;
    }

    if (step.value === 4) {
        captchaError.value = null;
        if (captchaEnabled.value && !form.altcha) {
            captchaError.value = 'Please complete the security verification.';

            return false;
        }

        return true;
    }

    return true;
}

function nextStep(): void {
    if (!validateCurrentStep()) {
        return;
    }
    if (step.value < 4) {
        step.value += 1;
    }
}

function prevStep(): void {
    clearZodErrors();
    uploadError.value = null;
    if (step.value > 0) {
        step.value -= 1;
    }
}

function onAltchaStateChange(e: Event): void {
    const detail = (e as CustomEvent<{ state?: string; payload?: string }>).detail;
    const state = detail?.state ?? '';
    const payload = detail?.payload ?? '';

    if (state === 'verified' && payload) {
        form.altcha = payload;
        captchaError.value = null;

        return;
    }

    if (state === 'error') {
        captchaError.value = 'Security check failed. Please try again.';
    }

    if (state === 'unverified' || state === 'verifying' || state === 'error') {
        form.altcha = '';
    }
}

watch(
    () => altchaRef.value,
    (el, oldEl) => {
        oldEl?.removeEventListener('statechange', onAltchaStateChange as EventListener);
        el?.addEventListener('statechange', onAltchaStateChange as EventListener);
    },
);

function submit(): void {
    if (!validateCurrentStep()) {
        return;
    }
    form.post('/online-enrollment', {
        preserveScroll: true,
        onError: () => {
            form.altcha = '';
        },
    });
}

async function uploadFile(collection: string, file: File | null): Promise<void> {
    uploadError.value = null;
    if (!file) {
        return;
    }
    if (captchaEnabled.value && !form.altcha) {
        uploadError.value = 'Please complete security verification before uploading documents.';

        return;
    }

    const body = new FormData();
    body.append('collection', collection);
    body.append('document', file);
    body.append('altcha', form.altcha || '');

    try {
        const { data } = await axios.post<{ id: string; collection: string; original_name: string }>('/enrollment/documents', body, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (data.collection === COLLECTION_EXTRA) {
            pendingUploads.value = [...pendingUploads.value, data];
        } else {
            pendingUploads.value = [...pendingUploads.value.filter((u) => u.collection !== data.collection), data];
        }
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422 && error.response.data?.errors?.altcha?.[0]) {
            uploadError.value = error.response.data.errors.altcha[0] as string;
            form.altcha = '';

            return;
        }

        uploadError.value = 'Upload failed. Please try a smaller file or a different format (JPG, PNG, PDF, WebP).';
    }
}

function onFileInput(collection: string, e: Event): void {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    void uploadFile(collection, file);
    input.value = '';
}

const reviewRows = computed(() => {
    const d = form.data();

    return [
        ['Name', [d.first_name, d.middle_name, d.last_name].filter(Boolean).join(' ')],
        ['Birth date', d.birth_date],
        ['Gender', d.gender],
        ['Address', d.address || '—'],
        ['Student phone', d.phone || '—'],
        ['Student email', d.email || '—'],
        ['Guardian', d.guardian_name],
        ['Guardian phone', d.guardian_phone],
        ['Relationship', d.guardian_relationship || '—'],
        ['Branch', props.branches.find((b) => b.id === d.branch_id)?.name ?? '—'],
        ['Grade level', props.gradeLevels.find((g) => g.id === d.grade_level_id)?.name ?? '—'],
        ['School year', props.schoolYear?.name ?? '—'],
        ['Notes', d.notes || '—'],
    ] as [string, string][];
});

const firstFormError = computed(() => {
    const e = form.errors;
    const keys = Object.keys(e);

    return keys.length ? (e[keys[0] as keyof typeof e] as string) : '';
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-amber-50 to-white">
        <Head title="Online enrollment" />

        <div v-if="unavailable || !schoolYear" class="mx-auto max-w-2xl px-4 py-20 text-center">
            <h1 class="text-2xl font-bold text-gray-900">Online enrollment unavailable</h1>
            <p class="mt-4 text-gray-600">Please check back later or contact the school office.</p>
            <a href="/" class="mt-8 inline-block rounded-lg bg-amber-600 px-6 py-2 text-white hover:bg-amber-700">Home</a>
        </div>

        <div v-else class="mx-auto max-w-3xl px-4 py-10 sm:py-14">
            <header class="mb-10 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Online enrollment</h1>
                <p class="mt-2 text-gray-600">Complete all steps to submit your application.</p>
                <p class="mt-1 text-sm text-amber-800">
                    School year: <span class="font-semibold">{{ schoolYear.name }}</span>
                </p>
            </header>

            <TabGroup :selected-index="step" as="div">
                <TabList class="sr-only" aria-hidden="true">
                    <Tab v-for="(label, i) in stepLabels" :key="label">Step {{ i + 1 }}: {{ label }}</Tab>
                </TabList>

                <ol class="mb-8 flex flex-wrap justify-center gap-2 sm:gap-3">
                    <li
                        v-for="(label, i) in stepLabels"
                        :key="label"
                        class="flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium sm:text-sm"
                        :class="
                            i === step
                                ? 'bg-amber-600 text-white'
                                : i < step
                                  ? 'bg-emerald-100 text-emerald-800'
                                  : 'bg-gray-100 text-gray-500'
                        "
                    >
                        <span class="tabular-nums">{{ i + 1 }}</span>
                        <span class="hidden sm:inline">{{ label }}</span>
                    </li>
                </ol>

                <TabPanels>
                    <TabPanel :unmount="false" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900">Personal information</h2>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">First name *</label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.first_name || form.errors.first_name" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.first_name || form.errors.first_name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Middle name</label>
                                <input
                                    v-model="form.middle_name"
                                    type="text"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last name *</label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.last_name || form.errors.last_name" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.last_name || form.errors.last_name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Birth date *</label>
                                <input
                                    v-model="form.birth_date"
                                    type="date"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.birth_date || form.errors.birth_date" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.birth_date || form.errors.birth_date }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Gender *</label>
                                <select
                                    v-model="form.gender"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                >
                                    <option disabled value="">Select…</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <p v-if="zodFieldErrors.gender || form.errors.gender" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.gender || form.errors.gender }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea
                                    v-model="form.address"
                                    rows="2"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                            </div>
                        </div>
                    </TabPanel>

                    <TabPanel :unmount="false" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900">Guardian & contact</h2>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Guardian full name *</label>
                                <input
                                    v-model="form.guardian_name"
                                    type="text"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.guardian_name || form.errors.guardian_name" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.guardian_name || form.errors.guardian_name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guardian phone *</label>
                                <input
                                    v-model="form.guardian_phone"
                                    type="text"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.guardian_phone || form.errors.guardian_phone" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.guardian_phone || form.errors.guardian_phone }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Relationship to student</label>
                                <input
                                    v-model="form.guardian_relationship"
                                    type="text"
                                    placeholder="e.g. Mother"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Student mobile (optional)</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.phone" class="mt-1 text-sm text-red-600">{{ zodFieldErrors.phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Student email (optional)</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.email || form.errors.email" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.email || form.errors.email }}
                                </p>
                            </div>
                        </div>
                    </TabPanel>

                    <TabPanel :unmount="false" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900">Academic preference</h2>
                        <div class="mt-6 grid gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Branch *</label>
                                <select
                                    v-model="form.branch_id"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                >
                                    <option :value="null">Select branch…</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }} ({{ b.code }})</option>
                                </select>
                                <p v-if="zodFieldErrors.branch_id || form.errors.branch_id" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.branch_id || form.errors.branch_id }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Grade level *</label>
                                <select
                                    v-model="form.grade_level_id"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                    :disabled="!form.branch_id"
                                >
                                    <option :value="null">Select grade…</option>
                                    <option v-for="g in filteredGradeLevels" :key="g.id" :value="g.id">{{ g.name }}</option>
                                </select>
                                <p v-if="zodFieldErrors.grade_level_id || form.errors.grade_level_id" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.grade_level_id || form.errors.grade_level_id }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Additional notes (optional)</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                />
                                <p v-if="zodFieldErrors.notes || form.errors.notes" class="mt-1 text-sm text-red-600">
                                    {{ zodFieldErrors.notes || form.errors.notes }}
                                </p>
                            </div>
                        </div>
                    </TabPanel>

                    <TabPanel :unmount="false" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900">Documents</h2>
                        <p class="mt-1 text-sm text-gray-600">Upload a clear photo and birth certificate (PDF or image). Optional: other supporting documents.</p>
                        <p v-if="uploadError" class="mt-3 text-sm text-red-600">{{ uploadError }}</p>
                        <div v-if="captchaEnabled" class="mt-6 rounded-lg bg-gray-50 px-4 py-3">
                            <p class="text-sm font-medium text-gray-800">Security verification</p>
                            <p class="mt-1 text-xs text-gray-500">Complete this once before uploading or submitting.</p>
                            <altcha-widget ref="altchaRef" class="mt-3 block min-h-[65px]" :challengeurl="altchaChallengeUrl ?? ''" />
                            <p v-if="captchaError" class="mt-2 text-sm text-red-600">{{ captchaError }}</p>
                            <p v-if="form.errors.altcha" class="mt-2 text-sm text-red-600">
                                {{ form.errors.altcha }}
                            </p>
                            <p class="mt-2 text-xs text-gray-400">This site uses ALTCHA proof-of-work to reduce spam.</p>
                        </div>
                        <div class="mt-6 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Student photo *</label>
                                <input type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-600" @change="onFileInput(COLLECTION_PHOTO, $event)" />
                                <p v-if="pendingUploads.some((u) => u.collection === COLLECTION_PHOTO)" class="mt-1 text-xs text-emerald-700">
                                    ✓
                                    {{ pendingUploads.find((u) => u.collection === COLLECTION_PHOTO)?.original_name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Birth certificate *</label>
                                <input type="file" accept=".pdf,image/*" class="mt-2 block w-full text-sm text-gray-600" @change="onFileInput(COLLECTION_BIRTH, $event)" />
                                <p v-if="pendingUploads.some((u) => u.collection === COLLECTION_BIRTH)" class="mt-1 text-xs text-emerald-700">
                                    ✓
                                    {{ pendingUploads.find((u) => u.collection === COLLECTION_BIRTH)?.original_name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Additional document (optional)</label>
                                <input type="file" accept=".pdf,image/*" class="mt-2 block w-full text-sm text-gray-600" @change="onFileInput(COLLECTION_EXTRA, $event)" />
                                <ul v-if="pendingUploads.filter((u) => u.collection === COLLECTION_EXTRA).length" class="mt-2 list-inside list-disc text-xs text-emerald-700">
                                    <li v-for="u in pendingUploads.filter((u) => u.collection === COLLECTION_EXTRA)" :key="u.id">{{ u.original_name }}</li>
                                </ul>
                            </div>
                        </div>
                    </TabPanel>

                    <TabPanel :unmount="false" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900">Review & submit</h2>
                        <p class="mt-1 text-sm text-gray-600">Please confirm your details before submitting.</p>
                        <dl class="mt-6 divide-y divide-gray-100 rounded-xl border border-gray-100">
                            <div v-for="(row, idx) in reviewRows" :key="idx" class="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">{{ row[0] }}</dt>
                                <dd class="text-sm text-gray-900 sm:col-span-2">{{ row[1] }}</dd>
                            </div>
                        </dl>
                        <div class="mt-6 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600">
                            <p class="font-medium text-gray-800">Documents</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                <li v-if="pendingUploads.some((u) => u.collection === COLLECTION_PHOTO)">Photo uploaded</li>
                                <li v-else class="text-amber-700">Photo missing — go back to upload</li>
                                <li v-if="pendingUploads.some((u) => u.collection === COLLECTION_BIRTH)">Birth certificate uploaded</li>
                                <li v-else class="text-amber-700">Birth certificate missing — go back to upload</li>
                                <li v-if="pendingUploads.some((u) => u.collection === COLLECTION_EXTRA)">Additional document(s) attached</li>
                            </ul>
                        </div>
                        <p v-if="captchaEnabled && !form.altcha" class="mt-4 text-sm text-amber-700">
                            Security verification is required. Please go back to the Documents step if this token expired.
                        </p>
                        <p v-if="firstFormError" class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800">
                            {{ firstFormError }}
                        </p>
                    </TabPanel>
                </TabPanels>
            </TabGroup>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-40"
                    :disabled="step === 0"
                    @click="prevStep"
                >
                    Back
                </button>
                <div class="flex gap-3">
                    <button
                        v-if="step < 4"
                        type="button"
                        class="rounded-lg bg-amber-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-amber-700"
                        @click="nextStep"
                    >
                        Next
                    </button>
                    <button
                        v-else
                        type="button"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700 disabled:opacity-50"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{ form.processing ? 'Submitting…' : 'Submit application' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

