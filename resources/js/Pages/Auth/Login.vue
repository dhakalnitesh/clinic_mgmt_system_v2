<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const showPassword = ref(false)

const submit = () => {
    form.post(route('login'))
}
</script>

<template>
    <Head title="Login" />

    <div class="h-screen bg-gray-100 dark:bg-black flex overflow-hidden">

        <!-- LEFT SIDE -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900">

            <!-- subtle pattern overlay -->
            <div class="absolute inset-0 opacity-[0.07] mix-blend-overlay" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>

            <div class="absolute inset-0 opacity-30">
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-emerald-300 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-teal-400 rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-between h-full w-full px-14 py-12 text-white">

                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center text-lg font-bold shadow-lg border border-white/10">
                            C
                        </div>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight">ClinicOS</h1>
                            <p class="text-emerald-100/80 text-xs mt-0.5">Healthcare Management Platform</p>
                        </div>
                    </div>

                    <div class="mt-16">
                        <p class="uppercase tracking-[0.3em] text-emerald-200/70 text-[11px] font-semibold mb-5">
                            Streamline Your Practice
                        </p>

                        <h2 class="text-[2rem] font-bold leading-[1.2] tracking-tight max-w-md">
                            The all-in-one platform to manage your clinic, patients, and revenue.
                        </h2>

                        <p class="mt-4 text-sm text-emerald-100/70 leading-relaxed max-w-sm">
                            From appointments to billing — everything you need to run a modern, efficient healthcare practice.
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-sm text-emerald-100/80">
                        <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Trusted by 1,000+ clinics nationwide</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-emerald-100/80">
                        <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>99.9% uptime — secure &amp; reliable</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 bg-white dark:bg-gray-950">

            <div class="w-full max-w-sm">

                <!-- MOBILE BRAND -->
                <div class="lg:hidden text-center mb-8">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 mx-auto flex items-center justify-center text-white text-lg font-bold shadow-lg mb-3">
                        C
                    </div>
                    <h1 class="text-xl font-black text-gray-900 dark:text-white">
                        ClinicOS
                    </h1>
                </div>

                <!-- Status message -->
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 rounded-xl px-4 py-3">
                    {{ status }}
                </div>

                <!-- FORM CARD -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl shadow-gray-200/40 dark:shadow-black/30 p-6 md:p-8">

                    <div class="mb-6">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-emerald-600 dark:text-emerald-400 mb-2">
                            Welcome Back
                        </p>

                        <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight">
                            Sign in to your account
                        </h2>

                        <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm leading-6">
                            Enter your credentials to continue.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">

                        <!-- EMAIL -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1.5">
                                Email Address
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="Enter your email"
                                class="w-full h-11 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/70 px-4 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none"
                            />

                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1.5">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- PASSWORD -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                                    Password
                                </label>

                                <Link
                                    :href="route('password.request')"
                                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
                                >
                                    Forgot Password?
                                </Link>
                            </div>

                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="Enter your password"
                                    class="w-full h-11 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/70 pl-4 pr-10 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none"
                                />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                </button>
                            </div>

                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1.5">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- REMEMBER -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />

                                <span class="text-xs text-gray-600 dark:text-gray-300">
                                    Remember me
                                </span>
                            </label>
                        </div>

                        <!-- BUTTON -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full h-11 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold text-sm shadow-lg shadow-emerald-500/20 transition-all duration-300 hover:-translate-y-0.5 disabled:opacity-70"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                                Signing in...
                            </span>
                            <span v-else>Sign In</span>
                        </button>
                    </form>

                    <!-- SIGNUP -->
                    <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-800 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-xs">
                            Don&rsquo;t have an account?

                            <Link
                                :href="route('register')"
                                class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-semibold"
                            >
                                Create Account
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
