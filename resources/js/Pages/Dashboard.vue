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

                <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-2xl p-5 shadow-lg text-white flex items-center gap-4">
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

            <!-- CHART -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm relative">
                <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-chart-area text-teal-600"></i>
                    Weekly Clinic Activity
                </h3>

                <div class="relative" @mouseleave="hoveredPoint = null">
                    <svg viewBox="0 0 700 200" class="w-full h-64" @mousemove="onChartHover">
                        <defs>
                            <linearGradient id="apptGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0d9488" stop-opacity="0.2"/>
                                <stop offset="100%" stop-color="#0d9488" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="visitGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#ef4444" stop-opacity="0.2"/>
                                <stop offset="100%" stop-color="#ef4444" stop-opacity="0"/>
                            </linearGradient>
                        </defs>

                        <line x1="0" y1="50" x2="700" y2="50" stroke="#e2e8f0" class="dark:stroke-gray-700"/>
                        <line x1="0" y1="100" x2="700" y2="100" stroke="#e2e8f0" class="dark:stroke-gray-700"/>
                        <line x1="0" y1="150" x2="700" y2="150" stroke="#e2e8f0" class="dark:stroke-gray-700"/>

                        <polygon :points="areaPoints('appointment')" fill="url(#apptGrad)"/>
                        <polygon :points="areaPoints('visit')" fill="url(#visitGrad)"/>

                        <polyline :points="appointmentPath" fill="none" stroke="#0d9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline :points="visitPath" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <g v-for="(p, i) in chartPoints" :key="i">
                            <circle :cx="p.x" :cy="p.appointmentY" r="4" fill="#0d9488" stroke="white" stroke-width="2"
                                class="cursor-pointer" @mouseenter="hoveredPoint = { ...weeklyData[i], type: 'appointment' }"/>
                            <circle :cx="p.x" :cy="p.visitY" r="4" fill="#ef4444" stroke="white" stroke-width="2"
                                class="cursor-pointer" @mouseenter="hoveredPoint = { ...weeklyData[i], type: 'visit' }"/>
                        </g>
                    </svg>

                    <div v-if="hoveredPoint"
                        class="absolute -top-2 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg px-3 py-2 shadow-lg whitespace-nowrap z-10 pointer-events-none"
                        :style="{ left: hoveredX + 'px' }">
                        <div class="font-semibold mb-1">{{ hoveredPoint.date }}</div>
                        <div class="flex gap-4">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-teal-600"></span> Appt: {{ hoveredPoint.appointments }}</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Visit: {{ hoveredPoint.visits }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-6 text-sm font-medium mt-3">
                    <span class="inline-flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-teal-600"></span>
                        Appointments
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        Visits
                    </span>
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

import CreatePatientModal from '@/Pages/Patients/CreateModal.vue'
import CreateDoctorModal from '@/Pages/Doctors/CreateModal.vue'
import CreateAppointmentModal from '@/Pages/Appointments/CreateModal.vue'

let refreshInterval = null

onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['todayAppointments', 'todayPatients', 'activeDoctors', 'todayVisits', 'weeklyData'] })
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

const hoveredPoint = ref(null)
const hoveredX = ref(0)

const weeklyData = computed(() => page.props.weeklyData ?? [])

const chartPoints = computed(() => {
    const width = 700
    const height = 200
    if (!weeklyData.value.length) return []
    const maxVal = Math.max(...weeklyData.value.flatMap(d => [d.appointments, d.visits]), 1)
    const stepX = width / (weeklyData.value.length - 1)
    return weeklyData.value.map((d, i) => ({
        x: i * stepX,
        appointmentY: height - (d.appointments / maxVal) * height,
        visitY: height - (d.visits / maxVal) * height,
    }))
})

const appointmentPath = computed(() =>
    chartPoints.value.map(p => `${p.x},${p.appointmentY}`).join(' ')
)

const visitPath = computed(() =>
    chartPoints.value.map(p => `${p.x},${p.visitY}`).join(' ')
)

const areaPoints = (type) => {
    const pts = chartPoints.value
    if (!pts.length) return ''
    const baseY = 200
    const yKey = type === 'appointment' ? 'appointmentY' : 'visitY'
    let d = `0,${baseY} `
    d += pts.map(p => `${p.x},${p[yKey]}`).join(' ')
    d += ` ${pts[pts.length - 1].x},${baseY}`
    return d
}

const onChartHover = (e) => {
    const rect = e.currentTarget.getBoundingClientRect()
    const mouseX = e.clientX - rect.left
    const svgWidth = rect.width
    const scale = svgWidth / 700
    const svgX = mouseX / scale
    const stepX = 700 / (weeklyData.value.length - 1)
    const idx = Math.round(svgX / stepX)
    const clamped = Math.max(0, Math.min(idx, weeklyData.value.length - 1))
    hoveredPoint.value = weeklyData.value[clamped]
    hoveredX.value = clamped * stepX * scale
}
</script>
