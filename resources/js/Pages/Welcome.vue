<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    appName?: string;
}>();

const page = usePage();

const displayName = computed(() => {
    const school = page.props.school as { name?: string } | undefined;

    return school?.name || (page.props.appName as string | undefined) || 'SFAMS';
});

const logoUrl = computed(() => {
    const school = page.props.school as { logo_url?: string | null } | undefined;

    return school?.logo_url || null;
});
</script>

<template>
    <Head title="Welcome to SFAMS - School Financial and Academic Management System" />

    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            alt=""
                            class="h-9 w-auto max-w-[160px] object-contain"
                        />
                        <span class="text-2xl font-bold bg-gradient-to-r from-amber-600 to-amber-500 bg-clip-text text-transparent">
                            {{ displayName }}
                        </span>
                    </div>
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-gray-600 hover:text-amber-600 transition">Features</a>
                        <a href="#benefits" class="text-gray-600 hover:text-amber-600 transition">Benefits</a>
                        <a href="#modules" class="text-gray-600 hover:text-amber-600 transition">Modules</a>
                        <a href="#contact" class="text-gray-600 hover:text-amber-600 transition">Contact</a>
                        <Link
                            href="/admin"
                            class="px-5 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition shadow-md"
                        >
                            Admin Login
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-amber-50 via-white to-amber-50">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                            Transform Your School Management
                            <span class="block bg-gradient-to-r from-amber-600 to-amber-500 bg-clip-text text-transparent">
                                with SFAMS
                            </span>
                        </h1>
                        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                            A comprehensive School Financial and Academic Management System designed to streamline operations, enhance efficiency, and empower educational institutions.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a
                                href="#features"
                                class="px-8 py-4 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition shadow-lg hover:shadow-xl font-semibold"
                            >
                                Explore Features
                            </a>
                            <Link
                                href="/online-enrollment"
                                class="px-8 py-4 bg-white text-amber-600 rounded-lg hover:bg-gray-50 transition border-2 border-amber-600 font-semibold inline-flex items-center justify-center"
                            >
                                Online enrollment
                            </Link>
                            <a
                                href="#contact"
                                class="px-8 py-4 bg-white text-amber-600 rounded-lg hover:bg-gray-50 transition border-2 border-amber-600 font-semibold"
                            >
                                Request Demo
                            </a>
                        </div>
                    </div>
                    <div class="relative">
                        <!-- Hero Image Placeholder -->
                        <div class="aspect-[4/3] bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl shadow-2xl flex items-center justify-center">
                            <div class="text-center p-8">
                                <svg class="w-24 h-24 mx-auto text-amber-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="text-amber-700 font-medium">Hero Image</p>
                                <p class="text-sm text-amber-600">Dashboard Preview</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-amber-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-bold text-white mb-2">100%</div>
                        <div class="text-amber-100">Digital</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-white mb-2">24/7</div>
                        <div class="text-amber-100">Access</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-white mb-2">Multi</div>
                        <div class="text-amber-100">Branch Support</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-white mb-2">Real-time</div>
                        <div class="text-amber-100">Updates</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Powerful Features</h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Everything your institution needs in one comprehensive platform
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-2xl transition border border-gray-100">
                        <div class="w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Student Management</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Comprehensive student profiles, enrollment tracking, academic records, and guardian information all in one place.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-2xl transition border border-gray-100">
                        <div class="w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Financial Management</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Streamlined payment processing, account tracking, automated billing, and comprehensive financial reporting.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-2xl transition border border-gray-100">
                        <div class="w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Academic Tracking</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Grade management, subject assignment, section organization, and detailed academic performance analytics.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-2xl transition border border-gray-100">
                        <div class="w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Multi-Branch Support</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Manage multiple school branches with individual accounts, commission tracking, and centralized oversight.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-2xl transition border border-gray-100">
                        <div class="w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Document Management</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Digital document storage, requirement tracking, verification workflows, and automated reminders.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-8 bg-white rounded-xl shadow-lg hover:shadow-2xl transition border border-gray-100">
                        <div class="w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Real-time Notifications</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Instant alerts for payments, enrollments, grade updates, and important announcements via email and SMS.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section with Image -->
        <section id="benefits" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <!-- Benefits Image Placeholder -->
                        <div class="aspect-square bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl shadow-xl flex items-center justify-center">
                            <div class="text-center p-8">
                                <svg class="w-32 h-32 mx-auto text-amber-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <p class="text-amber-700 font-medium">Benefits Image</p>
                                <p class="text-sm text-amber-600">School Analytics</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-4xl font-bold text-gray-900 mb-6">Why Choose SFAMS?</h2>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-amber-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Increase Efficiency</h3>
                                    <p class="text-gray-600">Automate repetitive tasks and reduce administrative workload by up to 70%.</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-amber-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Improve Accuracy</h3>
                                    <p class="text-gray-600">Eliminate manual errors with automated calculations and validation checks.</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-amber-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Enhanced Security</h3>
                                    <p class="text-gray-600">Role-based access control and comprehensive activity logging for data protection.</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-amber-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Better Insights</h3>
                                    <p class="text-gray-600">Real-time reports and analytics to make data-driven decisions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modules Section -->
        <section id="modules" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Complete System Modules</h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Integrated modules working together seamlessly
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="p-6 bg-white rounded-lg border-2 border-gray-100 hover:border-amber-600 transition">
                        <div class="text-amber-600 font-bold text-sm mb-2">STUDENT MODULE</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Enrollment & Records</h3>
                        <p class="text-gray-600 text-sm">Complete student lifecycle management from application to graduation</p>
                    </div>

                    <div class="p-6 bg-white rounded-lg border-2 border-gray-100 hover:border-amber-600 transition">
                        <div class="text-amber-600 font-bold text-sm mb-2">FINANCE MODULE</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Payments & Billing</h3>
                        <p class="text-gray-600 text-sm">Automated fee calculation, payment tracking, and account reconciliation</p>
                    </div>

                    <div class="p-6 bg-white rounded-lg border-2 border-gray-100 hover:border-amber-600 transition">
                        <div class="text-amber-600 font-bold text-sm mb-2">ACADEMIC MODULE</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Grades & Assessments</h3>
                        <p class="text-gray-600 text-sm">Grade entry, report card generation, and performance analytics</p>
                    </div>

                    <div class="p-6 bg-white rounded-lg border-2 border-gray-100 hover:border-amber-600 transition">
                        <div class="text-amber-600 font-bold text-sm mb-2">BRANCH MODULE</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Multi-Branch Management</h3>
                        <p class="text-gray-600 text-sm">Branch-specific operations with centralized reporting and oversight</p>
                    </div>

                    <div class="p-6 bg-white rounded-lg border-2 border-gray-100 hover:border-amber-600 transition">
                        <div class="text-amber-600 font-bold text-sm mb-2">DOCUMENT MODULE</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Requirements Tracking</h3>
                        <p class="text-gray-600 text-sm">Digital document submission, verification, and approval workflows</p>
                    </div>

                    <div class="p-6 bg-white rounded-lg border-2 border-gray-100 hover:border-amber-600 transition">
                        <div class="text-amber-600 font-bold text-sm mb-2">REPORTING MODULE</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Analytics & Reports</h3>
                        <p class="text-gray-600 text-sm">Comprehensive reports with export to Excel and PDF formats</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Screenshot/Demo Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-gray-900 to-gray-800 text-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4">See SFAMS in Action</h2>
                    <p class="text-xl text-gray-300">Modern, intuitive interface designed for efficiency</p>
                </div>

                <!-- Demo Screenshot Placeholder -->
                <div class="aspect-video bg-gradient-to-br from-gray-700 to-gray-600 rounded-2xl shadow-2xl flex items-center justify-center">
                    <div class="text-center p-8">
                        <svg class="w-32 h-32 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-300 font-medium text-lg">System Screenshot</p>
                        <p class="text-sm text-gray-400">Admin Dashboard Interface</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section id="contact" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-amber-600 to-amber-500">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-bold text-white mb-6">Ready to Transform Your School?</h2>
                <p class="text-xl text-amber-100 mb-8">
                    Join leading educational institutions using SFAMS to streamline their operations
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <button class="px-8 py-4 bg-white text-amber-600 rounded-lg hover:bg-gray-50 transition shadow-lg font-semibold">
                        Schedule a Demo
                    </button>
                    <button class="px-8 py-4 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition shadow-lg font-semibold">
                        Contact Sales
                    </button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-4 gap-8 mb-8">
                    <div>
                        <div class="text-2xl font-bold text-white mb-4">{{ appName || 'SFAMS' }}</div>
                        <p class="text-sm">
                            School Financial and Academic Management System
                        </p>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-4">Product</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#features" class="hover:text-white transition">Features</a></li>
                            <li><a href="#benefits" class="hover:text-white transition">Benefits</a></li>
                            <li><a href="#modules" class="hover:text-white transition">Modules</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-4">Support</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Documentation</a></li>
                            <li><a href="#" class="hover:text-white transition">Help Center</a></li>
                            <li><a href="#contact" class="hover:text-white transition">Contact Us</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-4">Legal</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 text-center text-sm">
                    <p>&copy; {{ new Date().getFullYear() }} {{ appName || 'SFAMS' }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
