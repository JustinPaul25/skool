<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false as boolean,
});

function submit(): void {
    form.post('/portal/login', {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="flex min-h-screen flex-col justify-center bg-zinc-100 px-4 py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h1 class="text-center text-2xl font-semibold tracking-tight text-zinc-900">Student portal</h1>
            <p class="mt-2 text-center text-sm text-zinc-600">Sign in with your school email and password.</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="rounded-xl bg-white px-6 py-8 shadow-sm ring-1 ring-zinc-200">
                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-700">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            name="email"
                            autocomplete="username"
                            required
                            class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-zinc-900 shadow-sm placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-700">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-zinc-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            name="remember"
                            class="size-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        <label for="remember" class="text-sm text-zinc-700">Remember me</label>
                    </div>

                    <button
                        type="submit"
                        class="flex w-full justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Signing in…' : 'Sign in' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
