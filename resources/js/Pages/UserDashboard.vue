<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <div class="space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-gray-100">
                        Welcome back, {{ userName }}
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        {{ currentDate }}
                    </p>
                </div>

                <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-2xl p-2 shadow-lg text-white flex items-center gap-4">
                    <div class="flex items-center justify-center size-12 rounded-xl bg-white/20">
                        <i class="fas fa-heartbeat text-2xl"></i>
                    </div>
                    <div class="text-sm">
                        <p class="font-bold text-lg leading-tight">ClinicOS</p>
                        <p class="opacity-80 text-teal-100">Management System</p>
                    </div>
                </div>
            </div>

            <!-- APPOINTMENT BOOKED CARD -->
            <div v-if="appointmentBooked && appointment"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-green-200 dark:border-green-800 shadow-sm p-5 flex items-start gap-4">
              <div class="flex items-center justify-center size-10 rounded-full bg-green-50 dark:bg-green-900/30 shrink-0 mt-0.5">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
              </div>
              <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                <p class="font-bold text-base text-gray-900 dark:text-gray-100">Appointment Booked</p>
                <p><span class="font-medium text-gray-500 dark:text-gray-400">Date (BS):</span> {{ appointment.date_np }}</p>
                <p><span class="font-medium text-gray-500 dark:text-gray-400">Date (AD):</span> {{ appointment.date_en }}</p>
                <p><span class="font-medium text-gray-500 dark:text-gray-400">Doctor:</span> {{ appointment.doctor_name }}</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs pt-1">Please visit the clinic on time.</p>
              </div>
            </div>

            <!-- INFO CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="text-2xl p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20">
                        <i class="fas fa-user-md text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Active Doctors
                        </p>
                        <p class="text-2xl font-black text-gray-800 dark:text-gray-100">{{ activeDoctors }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="text-2xl p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20">
                        <i class="fas fa-calendar-check text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Today's Appointments
                        </p>
                        <p class="text-2xl font-black text-gray-800 dark:text-gray-100">{{ todayAppointments }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="text-2xl p-3 rounded-xl bg-green-50 dark:bg-green-900/20">
                        <i class="fas fa-users text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Total Patients
                        </p>
                        <p class="text-2xl font-black text-gray-800 dark:text-gray-100">{{ todayPatients }}</p>
                    </div>
                </div>

            </div>

            <!-- QUICK LINKS -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-link text-teal-600"></i>
                    Quick Links
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <Link href="/doctors"
                        class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 font-bold hover:shadow-md transition-all text-sm border border-purple-100 dark:border-purple-900/30 text-center">
                        <i class="fas fa-user-md mr-2"></i>View Doctors
                    </Link>
                    <Link href="/guest/patients/create"
                        class="p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 font-bold hover:shadow-md transition-all text-sm border border-teal-100 dark:border-teal-900/30 text-center">
                        <i class="fas fa-user-plus mr-2"></i>Register Patient
                    </Link>
                    <Link href="/guest/appointments/create"
                        class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-bold hover:shadow-md transition-all text-sm border border-blue-100 dark:border-blue-900/30 text-center">
                        <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                    </Link>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const page = usePage()

const user = computed(() => page.props.auth?.user ?? {})
const userName = computed(() => user.value?.name || 'User')

const currentDate = computed(() =>
    new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
)

const todayAppointments = computed(() => page.props.todayAppointments ?? 0)
const todayPatients = computed(() => page.props.todayPatients ?? 0)
const activeDoctors = computed(() => page.props.activeDoctors ?? 0)
const appointmentBooked = computed(() => page.props.appointmentBooked ?? false)
const appointment = computed(() => page.props.appointment ?? null)
</script>
