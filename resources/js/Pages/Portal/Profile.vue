<script setup lang="ts">
import PortalLayout from '../../Layouts/PortalLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

defineOptions({
    layout: PortalLayout,
});

type StudentRow = {
    student_id: string;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    full_name: string;
    birth_date: string | null;
    gender: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    guardian_name: string;
    guardian_phone: string;
    guardian_relationship: string | null;
    branch: string | null;
    photo_url: string | null;
};

const page = usePage<{ student: StudentRow }>();

const student = page.props.student;

const form = useForm({
    phone: student.phone ?? '',
    address: student.address ?? '',
});

function submit(): void {
    form.patch('/portal/profile', { preserveScroll: true });
}
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Profile</h1>
            <p class="mt-1 text-sm text-zinc-600">View your information. Phone and address can be updated below.</p>
        </div>

        <div class="flex flex-col gap-8 lg:flex-row">
            <div class="shrink-0">
                <div
                    class="flex size-32 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 text-zinc-400"
                >
                    <img
                        v-if="student.photo_url"
                        :src="student.photo_url"
                        alt=""
                        class="size-full object-cover"
                    />
                    <span v-else class="text-4xl font-medium text-zinc-300">
                        {{ student.first_name.charAt(0) }}{{ student.last_name.charAt(0) }}
                    </span>
                </div>
                <p class="mt-2 text-center text-xs text-zinc-500">School photo (managed by the office)</p>
            </div>

            <div class="min-w-0 flex-1 space-y-6">
                <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-zinc-900">Student</h2>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-zinc-500">Full name</dt>
                            <dd class="font-medium text-zinc-900">{{ student.full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Student ID</dt>
                            <dd class="font-mono font-medium text-zinc-900">{{ student.student_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Birth date</dt>
                            <dd class="text-zinc-900">{{ student.birth_date ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Gender</dt>
                            <dd class="capitalize text-zinc-900">{{ student.gender }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Email</dt>
                            <dd class="text-zinc-900">{{ student.email ?? '—' }}</dd>
                        </div>
                        <div v-if="student.branch">
                            <dt class="text-zinc-500">Branch</dt>
                            <dd class="text-zinc-900">{{ student.branch }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-zinc-900">Guardian</h2>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-zinc-500">Name</dt>
                            <dd class="text-zinc-900">{{ student.guardian_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Phone</dt>
                            <dd class="text-zinc-900">{{ student.guardian_phone }}</dd>
                        </div>
                        <div v-if="student.guardian_relationship">
                            <dt class="text-zinc-500">Relationship</dt>
                            <dd class="text-zinc-900">{{ student.guardian_relationship }}</dd>
                        </div>
                    </dl>
                </section>

                <form class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
                    <h2 class="text-sm font-semibold text-zinc-900">Contact (editable)</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-zinc-700">Phone</label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                class="mt-1 block w-full max-w-md rounded-lg border border-zinc-300 px-3 py-2 text-zinc-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-zinc-700">Address</label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1 block w-full max-w-lg rounded-lg border border-zinc-300 px-3 py-2 text-zinc-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                            />
                            <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                        </div>
                    </div>
                    <button
                        type="submit"
                        class="mt-6 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving…' : 'Save changes' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
