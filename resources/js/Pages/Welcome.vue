<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin:    { type: Boolean },
    canRegister: { type: Boolean },
    appVersion:  { type: String, default: '1.0.0' },
    phpVersion:  { type: String, required: true },
});

const isDark  = ref(false);
const mounted = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('theme');
    isDark.value = saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
    applyTheme();
    setTimeout(() => { mounted.value = true; }, 50);
});

function toggleDark() {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    applyTheme();
}

function applyTheme() {
    document.documentElement.classList.toggle('dark', isDark.value);
}

// ─── FIX: icons are plain SVG strings, rendered via v-html ───────────────
const features = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`,
        title: 'Patient Management',
        desc:  'Comprehensive patient profiles, medical history, visit records, and real-time status tracking — all in one unified system.',
        href:  '#',
        accent: 'teal',
        large:  true,
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" /></svg>`,
        title: 'Appointment Scheduling',
        desc:  'Smart calendar with conflict detection, automated reminders, and multi-doctor support.',
        href:  '#',
        accent: 'blue',
        large:  false,
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>`,
        title: 'Digital Prescriptions',
        desc:  'Generate, manage, and track prescriptions with drug interaction alerts and dosage history.',
        href:  '#',
        accent: 'purple',
        large:  false,
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>`,
        title: 'Billing & Invoicing',
        desc:  'Automated billing, insurance claims, payment tracking, and detailed financial reports.',
        href:  '#',
        accent: 'amber',
        large:  false,
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>`,
        title: 'Lab & Reports',
        desc:  'Integrate lab results, imaging reports, and diagnostics directly into patient records.',
        href:  '#',
        accent: 'rose',
        large:  false,
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>`,
        title: 'Analytics Dashboard',
        desc:  'Real-time clinic insights: patient flow, revenue trends, doctor performance, and inventory metrics.',
        href:  '#',
        accent: 'green',
        large:  false,
    },
];

const accentMap = {
    teal:   { bg: 'bg-teal-50 dark:bg-teal-900/20',     icon: 'text-teal-600 dark:text-teal-400',     ring: 'ring-teal-200 dark:ring-teal-800',     hover: 'hover:ring-teal-400 dark:hover:ring-teal-600'   },
    blue:   { bg: 'bg-blue-50 dark:bg-blue-900/20',     icon: 'text-blue-600 dark:text-blue-400',     ring: 'ring-blue-200 dark:ring-blue-800',     hover: 'hover:ring-blue-400 dark:hover:ring-blue-600'   },
    purple: { bg: 'bg-purple-50 dark:bg-purple-900/20', icon: 'text-purple-600 dark:text-purple-400', ring: 'ring-purple-200 dark:ring-purple-800', hover: 'hover:ring-purple-400 dark:hover:ring-purple-600' },
    amber:  { bg: 'bg-amber-50 dark:bg-amber-900/20',   icon: 'text-amber-600 dark:text-amber-400',   ring: 'ring-amber-200 dark:ring-amber-800',   hover: 'hover:ring-amber-400 dark:hover:ring-amber-600'  },
    rose:   { bg: 'bg-rose-50 dark:bg-rose-900/20',     icon: 'text-rose-600 dark:text-rose-400',     ring: 'ring-rose-200 dark:ring-rose-800',     hover: 'hover:ring-rose-400 dark:hover:ring-rose-600'   },
    green:  { bg: 'bg-green-50 dark:bg-green-900/20',   icon: 'text-green-600 dark:text-green-400',   ring: 'ring-green-200 dark:ring-green-800',   hover: 'hover:ring-green-400 dark:hover:ring-green-600'  },
};

const stats = [
    { label: 'Patients Managed',    value: '10,000+' },
    { label: 'Appointments Daily',  value: '500+'    },
    { label: 'Clinics Powered',     value: '120+'    },
    { label: 'Uptime SLA',          value: '99.9%'   },
];
</script>

<template>
    <Head title="Welcome — ClinicOS" />

    <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-[#0b0f1a] dark:text-slate-200 transition-colors duration-300 font-sans antialiased">

        <!-- ── Decorative background orbs ──────────────────────────────── -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-teal-400/10 dark:bg-teal-500/5 blur-3xl"></div>
            <div class="absolute top-1/3 -left-32 w-[400px] h-[400px] rounded-full bg-blue-400/10 dark:bg-blue-600/5 blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-[500px] h-[300px] rounded-full bg-purple-400/10 dark:bg-purple-600/5 blur-3xl"></div>
        </div>

        <div class="relative flex min-h-screen flex-col">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col flex-1">

                <!-- ── Header ───────────────────────────────────────────── -->
                <header class="flex items-center justify-between py-6 lg:py-8">

                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-teal-700 shadow-lg shadow-teal-500/25">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                <circle cx="12" cy="12" r="10" stroke-width="1.5" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">
                                Clinic<span class="text-teal-600 dark:text-teal-400">OS</span>
                            </span>
                            <p class="text-[10px] text-slate-500 dark:text-slate-500 leading-none -mt-0.5 tracking-widest uppercase">Management System</p>
                        </div>
                    </div>

                    <!-- Right controls -->
                    <div class="flex items-center gap-2">

                        <!-- Dark mode toggle -->
                        <button
                            @click="toggleDark"
                            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                            class="relative flex h-9 w-9 items-center justify-center rounded-lg ring-1 ring-slate-200 dark:ring-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200"
                        >
                            <!-- Sun (shown in dark mode) -->
                            <svg v-if="isDark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <!-- Moon (shown in light mode) -->
                            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                        </button>

                        <!-- Nav links -->
                        <nav v-if="canLogin" class="flex items-center gap-1">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="flex items-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition-colors duration-200 shadow-sm"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </Link>

                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200"
                                >
                                    Log in
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition-colors duration-200 shadow-sm"
                                >
                                    Get Started
                                </Link>
                            </template>
                        </nav>
                    </div>
                </header>

                <!-- ── Hero ─────────────────────────────────────────────── -->
                <section class="pt-10 pb-16 lg:pt-16 lg:pb-20 text-center">

                    <div class="inline-flex items-center gap-2 rounded-full bg-teal-50 dark:bg-teal-900/30 px-4 py-1.5 ring-1 ring-teal-200 dark:ring-teal-800 mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                        <span class="text-xs font-semibold text-teal-700 dark:text-teal-300 tracking-wide uppercase">All-in-One Healthcare Platform</span>
                    </div>

                    <h1 class="mx-auto max-w-4xl text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl leading-tight">
                        Modern Clinic Management
                        <span class="block mt-1 bg-gradient-to-r from-teal-500 via-teal-600 to-blue-600 bg-clip-text text-transparent">
                            Built for Healthcare
                        </span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                        Streamline patient care, appointments, billing, and reporting — everything your clinic needs, unified in one powerful and intuitive system.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-6 py-3 text-sm font-semibold text-white hover:bg-teal-700 transition-all duration-200 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 hover:-translate-y-0.5"
                        >
                            Start Free Trial
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                        <Link
                            v-if="canLogin && !$page.props.auth.user"
                            :href="route('login')"
                            class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200"
                        >
                            Sign in to your account
                        </Link>
                    </div>
                </section>

                <!-- ── Stats strip ──────────────────────────────────────── -->
                <section class="mb-12">
                    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                        <div
                            v-for="stat in stats"
                            :key="stat.label"
                            class="rounded-2xl bg-white dark:bg-slate-800/50 px-5 py-4 ring-1 ring-slate-200 dark:ring-slate-700/50 text-center"
                        >
                            <p class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ stat.value }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400 font-medium">{{ stat.label }}</p>
                        </div>
                    </div>
                </section>

                <!-- ── Features grid ────────────────────────────────────── -->
                <main class="pb-16">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">

                        <a
                            v-for="(f, i) in features"
                            :key="f.title"
                            :href="f.href"
                            :class="[
                                'group relative flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-slate-800/50 p-6 ring-1 transition-all duration-300',
                                'hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-slate-900/50',
                                accentMap[f.accent].ring,
                                accentMap[f.accent].hover,
                                f.large ? 'lg:row-span-2 lg:p-8' : '',
                            ]"
                        >
                            <!-- Icon wrapper — v-html renders the SVG string safely -->
                            <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl mb-5', accentMap[f.accent].bg]">
                                <div
                                    :class="['h-6 w-6', accentMap[f.accent].icon]"
                                    v-html="f.icon"
                                ></div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1">
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ f.title }}</h2>
                                <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ f.desc }}</p>
                            </div>

                            <!-- Arrow link -->
                            <div class="mt-5 flex items-center gap-1.5">
                                <span :class="['text-xs font-semibold', accentMap[f.accent].icon]">Explore feature</span>
                                <svg
                                    :class="['h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-1', accentMap[f.accent].icon]"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>

                            <!-- Corner glow -->
                            <div
                                :class="['pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full opacity-10 group-hover:opacity-20 transition-opacity duration-300', accentMap[f.accent].bg]"
                            ></div>
                        </a>

                        <!-- CTA card -->
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-teal-600 to-teal-800 dark:from-teal-700 dark:to-teal-900 p-6 lg:col-span-2 ring-1 ring-teal-500/50">
                            <div class="relative z-10">
                                <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white mb-4">Ready to get started?</span>
                                <h2 class="text-xl font-bold text-white lg:text-2xl">Transform how your clinic operates today</h2>
                                <p class="mt-2 text-sm text-teal-100 leading-relaxed max-w-md">
                                    Join hundreds of clinics already using ClinicOS to deliver better patient outcomes and run more efficiently.
                                </p>
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <Link
                                        v-if="canRegister"
                                        :href="route('register')"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-teal-700 hover:bg-teal-50 transition-colors duration-200"
                                    >
                                        Create Free Account
                                    </Link>
                                    <Link
                                        v-if="canLogin && !$page.props.auth.user"
                                        :href="route('login')"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-teal-500/30 px-5 py-2.5 text-sm font-semibold text-white ring-1 ring-white/30 hover:bg-teal-500/50 transition-colors duration-200"
                                    >
                                        Sign In
                                    </Link>
                                </div>
                            </div>
                            <!-- Decorative circles -->
                            <div class="pointer-events-none absolute -right-10 -bottom-10 h-48 w-48 rounded-full bg-white/5"></div>
                            <div class="pointer-events-none absolute -right-4  -bottom-4  h-28 w-28 rounded-full bg-white/5"></div>
                            <div class="pointer-events-none absolute top-4 right-20    h-16 w-16 rounded-full bg-white/5"></div>
                        </div>

                    </div>
                </main>

                <!-- ── Footer ───────────────────────────────────────────── -->
                <footer class="mt-auto border-t border-slate-200 dark:border-slate-800 py-8">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-md bg-teal-600">
                                <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">ClinicOS</span>
                        </div>

                        <p class="text-xs text-slate-400 dark:text-slate-500 text-center">
                            ClinicOS v{{ appVersion }} &nbsp;·&nbsp; PHP v{{ phpVersion }} &nbsp;·&nbsp; Built with Laravel &amp; Vue
                        </p>

                        <div class="flex items-center gap-4 text-xs text-slate-400 dark:text-slate-500">
                            <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Privacy</a>
                            <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Terms</a>
                            <a href="#" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Support</a>
                        </div>
                    </div>
                </footer>

            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

* { font-family: 'Inter', sans-serif; }

.font-sans { font-family: 'Inter', sans-serif; }

/*
  Only transition color/background — do NOT include 'transform' here.
  Including transform causes hover:-translate-y-1 to fight with the
  transition and produce a janky animation.
*/
*, *::before, *::after {
    transition-property: background-color, border-color, color;
    transition-duration: 150ms;
    transition-timing-function: ease;
}

/* Restore full transition only on interactive elements */
a, button {
    transition: all 200ms ease;
}
</style>