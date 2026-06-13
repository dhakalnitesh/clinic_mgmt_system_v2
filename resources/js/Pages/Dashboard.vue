<template>
    <AuthenticatedLayout>
        <Head title="Clinic Dashboard" />

        <div class="space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-gray-100">
                        Clinic Dashboard
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Real-time Patients, Doctors & Appointments Overview
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

            <!-- STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

                <div
                    v-for="stat in statsWithIcons"
                    :key="stat.label"
                    @click="goToOrOpen(stat)"
                    class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-4 cursor-pointer hover:shadow-md hover:scale-[1.02] transition-all"
                >
                    <div class="text-2xl p-3 rounded-xl" :class="stat.bgClass">
                        <i :class="[stat.icon, stat.colorClass]"></i>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ stat.label }}
                        </p>
                        <p class="text-2xl font-black text-gray-800 dark:text-gray-100">
                            {{ stat.value }}
                        </p>
                    </div>
                </div>

            </div>

            <!-- MAIN GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- QUICK ACTIONS -->
                <div class="col-span-12 lg:col-span-8 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">

                    <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-bolt text-teal-600"></i>
                        Quick Actions
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <button @click="showPatient = true"
                            class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 font-bold hover:shadow-md transition-all text-sm border border-blue-100 dark:border-blue-900/30">
                            <i class="fas fa-user-plus mr-2"></i>+ Add Patient
                        </button>

                        <button @click="showAppointment = true"
                            class="p-4 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 font-bold hover:shadow-md transition-all text-sm border border-green-100 dark:border-green-900/30">
                            <i class="fas fa-calendar-plus mr-2"></i>+ Book Appointment
                        </button>

                        <button @click="showDoctor = true"
                            class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 font-bold hover:shadow-md transition-all text-sm border border-purple-100 dark:border-purple-900/30">
                            <i class="fas fa-user-md mr-2"></i>+ Add Doctor
                        </button>

                        <button @click="goTo('/visits')"
                            class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 font-bold hover:shadow-md transition-all text-sm border border-rose-100 dark:border-rose-900/30">
                         <i class="fa-solid fa-person-walking"></i>
                            View Visits
                        </button>

                        <button @click="goTo('/billing/invoices')"
                            class="p-4 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 font-bold hover:shadow-md transition-all text-sm border border-yellow-100 dark:border-yellow-900/30">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>Billing
                        </button>

                        <button @click="goTo('/reports')"
                            class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 font-bold hover:shadow-md transition-all text-sm border border-gray-200 dark:border-gray-600">
                            <i class="fas fa-chart-bar mr-2"></i>Reports
                        </button>
                    </div>
                </div>

                <!-- RIGHT - Today's Summary -->
                <div class="col-span-12 lg:col-span-4 space-y-4">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <h3 class="font-bold mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <i class="fas fa-calendar-day text-teal-600"></i>
                            Today's Summary
                        </h3>

                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-calendar-check text-blue-500 mr-2"></i>Appointments
                                </span>
                                <b class="text-gray-900 dark:text-gray-100 text-lg">{{ todayAppointments }}</b>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-users text-green-500 mr-2"></i>Patients
                                </span>
                                <b class="text-gray-900 dark:text-gray-100 text-lg">{{ todayPatients }}</b>
                            </div>

                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-user-md text-purple-500 mr-2"></i>Active Doctors
                                </span>
                                <b class="text-gray-900 dark:text-gray-100 text-lg">{{ activeDoctors }}</b>
                            </div>

                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-walk text-rose-500 mr-2"></i>Visits
                                </span>
                                <b class="text-gray-900 dark:text-gray-100 text-lg">{{ todayVisits }}</b>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- CHARTS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Weekly Activity -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-teal-600"></i>
                        Weekly Activity (Nepali Dates)
                    </h3>

                    <div class="relative" style="height: 260px">
                        <Bar
                            v-if="weeklyChartData"
                            :data="weeklyChartData"
                            :options="chartOptions"
                        />
                    </div>
                </div>

                <!-- Monthly Activity -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-chart-line text-teal-600"></i>
                        Monthly Activity (Nepali Dates)
                    </h3>

                    <div class="relative" style="height: 260px">
                        <Bar
                            v-if="monthlyChartData"
                            :data="monthlyChartData"
                            :options="chartOptions"
                        />
                    </div>
                </div>

            </div>

        </div>

        <!-- MODALS -->
        <CreatePatientModal
            v-if="showPatient"
            @close="showPatient = false"
        />
        <CreateDoctorModal
            v-if="showDoctor"
            @close="showDoctor = false"
        />
        <CreateAppointmentModal
            v-if="showAppointment"
            @close="showAppointment = false"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js'

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
)

import CreatePatientModal from '@/Pages/Patients/CreateModal.vue'
import CreateDoctorModal from '@/Pages/Doctors/CreateModal.vue'
import CreateAppointmentModal from '@/Pages/Appointments/CreateModal.vue'

let refreshInterval = null

onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['todayAppointments', 'todayPatients', 'activeDoctors', 'todayVisits', 'weeklyData', 'monthlyData'] })
    }, 30000)
})

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval)
})

const page = usePage()

const showPatient = ref(false)
const showDoctor = ref(false)
const showAppointment = ref(false)

const goTo = (url) => router.visit(url)

const todayAppointments = computed(() => page.props.todayAppointments ?? 0)
const todayPatients = computed(() => page.props.todayPatients ?? 0)
const activeDoctors = computed(() => page.props.activeDoctors ?? 0)
const todayVisits = computed(() => page.props.todayVisits ?? 0)

const goToOrOpen = (stat) => {
    router.visit(stat.route)
}

const statsWithIcons = computed(() => [
    { label: 'Appointments', value: todayAppointments.value, icon: 'fas fa-calendar-check', route: '/appointments', bgClass: 'bg-blue-50 dark:bg-blue-900/20', colorClass: 'text-blue-600 dark:text-blue-400' },
    { label: 'Patients', value: todayPatients.value, icon: 'fas fa-users', route: '/patients', bgClass: 'bg-green-50 dark:bg-green-900/20', colorClass: 'text-green-600 dark:text-green-400' },
    { label: 'Doctors', value: activeDoctors.value, icon: 'fas fa-user-md', route: '/doctors', bgClass: 'bg-purple-50 dark:bg-purple-900/20', colorClass: 'text-purple-600 dark:text-purple-400' },
    { label: 'Visits', value: todayVisits.value, icon: 'fas fa-person-walking', route: '/visits', bgClass: 'bg-rose-50 dark:bg-rose-900/20', colorClass: 'text-rose-600 dark:text-rose-400' },
])

const weeklyData = computed(() => page.props.weeklyData ?? [])
const monthlyData = computed(() => page.props.monthlyData ?? [])

const weeklyChartData = computed(() => {
    if (!weeklyData.value.length) return null
    return {
        labels: weeklyData.value.map(d => d.nepali_date),
        datasets: [
            {
                label: 'Appointments',
                data: weeklyData.value.map(d => d.appointments),
                backgroundColor: 'rgba(13, 148, 136, 0.7)',
                hoverBackgroundColor: 'rgba(13, 148, 136, 0.9)',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Visits',
                data: weeklyData.value.map(d => d.visits),
                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                hoverBackgroundColor: 'rgba(239, 68, 68, 0.9)',
                borderRadius: 6,
                borderSkipped: false,
            },
        ],
    }
})

const monthlyChartData = computed(() => {
    if (!monthlyData.value.length) return null
    return {
        labels: monthlyData.value.map(d => d.nepali_date),
        datasets: [
            {
                label: 'Appointments',
                data: monthlyData.value.map(d => d.appointments),
                backgroundColor: 'rgba(99, 102, 241, 0.7)',
                hoverBackgroundColor: 'rgba(99, 102, 241, 0.9)',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Visits',
                data: monthlyData.value.map(d => d.visits),
                backgroundColor: 'rgba(251, 146, 60, 0.7)',
                hoverBackgroundColor: 'rgba(251, 146, 60, 0.9)',
                borderRadius: 6,
                borderSkipped: false,
            },
        ],
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            align: 'end',
            labels: {
                usePointStyle: true,
                padding: 20,
                font: { size: 12 },
            },
        },
        tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 13, weight: '600' },
            bodyFont: { size: 12 },
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
                title: (items) => items[0].label,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: {
                font: { size: 11 },
                color: '#94a3b8',
                maxRotation: 0,
            },
        },
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                precision: 0,
                font: { size: 11 },
                color: '#94a3b8',
            },
            grid: {
                color: 'rgba(148, 163, 184, 0.15)',
                drawBorder: false,
            },
        },
    },
}
</script>
