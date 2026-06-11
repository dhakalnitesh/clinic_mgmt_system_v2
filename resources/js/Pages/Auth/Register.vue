<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Register" />

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
                            Get Started Today
                        </p>

                        <h2 class="text-[2rem] font-bold leading-[1.2] tracking-tight max-w-md">
                            Create your account and start managing your clinic in minutes.
                        </h2>

                        <p class="mt-4 text-sm text-emerald-100/70 leading-relaxed max-w-sm">
                            Join thousands of healthcare professionals who trust ClinicOS for their daily operations.
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-sm text-emerald-100/80">
                        <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Free 14-day trial — no credit card needed</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-emerald-100/80">
                        <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>24/7 support — we're here to help</span>
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

                <!-- FORM CARD -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl shadow-gray-200/40 dark:shadow-black/30 p-6 md:p-8">

                    <div class="mb-6">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-emerald-600 dark:text-emerald-400 mb-2">
                            Get Started
                        </p>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight">
                            Create your account
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm leading-6">
                            Fill in the details to start using ClinicOS.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">

                        <!-- NAME -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1.5">
                                Full Name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Enter your full name"
                                class="w-full h-11 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/70 px-4 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none"
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1.5">{{ form.errors.name }}</p>
                        </div>

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
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1.5">{{ form.errors.email }}</p>
                        </div>

                        <!-- PASSWORD -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1.5">
                                Password
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="Min. 8 characters"
                                    class="w-full h-11 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/70 pl-4 pr-10 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none"
                                />
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1.5">{{ form.errors.password }}</p>
                        </div>

                        <!-- CONFIRM PASSWORD -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1.5">
                                Confirm Password
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    placeholder="Re-enter password"
                                    class="w-full h-11 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/70 pl-4 pr-10 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition outline-none"
                                />
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <i :class="showConfirmPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- BUTTON -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full h-11 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold text-sm shadow-lg shadow-emerald-500/20 transition-all duration-300 hover:-translate-y-0.5 disabled:opacity-70"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                                Creating account...
                            </span>
                            <span v-else>Create Account</span>
                        </button>
                    </form>

                    <!-- SIGNIN -->
                    <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-800 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-xs">
                            Already have an account?
                            <Link
                                :href="route('login')"
                                class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-semibold"
                            >
                                Sign in
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
