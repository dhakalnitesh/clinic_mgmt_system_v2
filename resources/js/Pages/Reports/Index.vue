<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    stats: Object,
})

const cards = [
    { label: 'Total Patients', value: props.stats?.totalPatients || 0, icon: 'fa-hospital-user', color: 'indigo' },
    { label: 'Total Appointments', value: props.stats?.totalAppointments || 0, icon: 'fa-calendar-check', color: 'blue' },
    { label: 'Total Visits', value: props.stats?.totalVisits || 0, icon: 'fa-notes-medical', color: 'teal' },
    { label: 'Total Consultations', value: props.stats?.totalConsultations || 0, icon: 'fa-stethoscope', color: 'purple' },
    { label: "Today's Appointments", value: props.stats?.todayAppointments || 0, icon: 'fa-calendar-day', color: 'amber' },
    { label: "Today's Visits", value: props.stats?.todayVisits || 0, icon: 'fa-walk', color: 'green' },
    { label: 'Total Revenue', value: 'Rs. ' + (props.stats?.totalRevenue || 0).toLocaleString(), icon: 'fa-rupee-sign', color: 'emerald' },
    { label: 'Pending Revenue', value: 'Rs. ' + (props.stats?.pendingRevenue || 0).toLocaleString(), icon: 'fa-clock', color: 'orange' },
]

const reportLinks = [
    { label: 'Appointment Report', route: 'reports.appointments', icon: 'fa-calendar-check' },
    { label: 'Revenue Report', route: 'reports.revenue', icon: 'fa-chart-line' },
    { label: 'Patient Report', route: 'reports.patients', icon: 'fa-users' },
]
</script>

<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-teal-600"></i>
                        Reports & Analytics
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Overview of clinic statistics and performance</p>
                </div>
            </div>

            <!-- Quick Export Buttons -->
            <div class="flex flex-wrap gap-2">
                <a :href="route('reports.appointments.export')"
                    class="text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-file-csv mr-1"></i> Export Appointments
                </a>
                <a :href="route('reports.revenue.export')"
                    class="text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-file-csv mr-1"></i> Export Revenue
                </a>
                <a :href="route('reports.patients.export')"
                    class="text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-file-csv mr-1"></i> Export Patients
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="card in cards" :key="card.label"
                    class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ card.label }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">{{ card.value }}</p>
                        </div>
                        <div class="p-3 rounded-lg" :class="'bg-' + card.color + '-50'">
                            <i :class="'fas ' + card.icon + ' text-' + card.color + '-600 text-xl'"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link v-for="link in reportLinks" :key="link.label" :href="route(link.route)"
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-lg bg-indigo-50">
                            <i :class="'fas ' + link.icon + ' text-indigo-600 text-xl'"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ link.label }}</h3>
                            <p class="text-sm text-gray-500">View detailed report</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>