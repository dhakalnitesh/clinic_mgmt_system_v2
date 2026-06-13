<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
    appointments: Object,
    filters: Object,
})
</script>

<template>
    <Head title="Appointment Report" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('reports.index')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-xl"></i></Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Appointment Report</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Detailed view of all appointments</p>
                    </div>
                </div>
                <a :href="route('reports.appointments.export')"
                    class="text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
            </div>

            <FilterBar route-name="reports.appointments" :filters="filters" search-placeholder="Search..." />

            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Patient</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Doctor</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Time</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(a, i) in appointments?.data || []" :key="a.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm">{{ ((appointments.current_page - 1) * appointments.per_page) + i + 1 }}</td>
                                <td class="px-6 py-4">{{ a.patient?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">Dr. {{ a.doctor?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">{{ a.appointment_date }}</td>
                                <td class="px-6 py-4 text-sm">{{ a.appointment_time || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="{
                                        'bg-emerald-100 text-emerald-700': a.status === 'completed' || a.status === 'visited',
                                        'bg-red-100 text-red-700': a.status === 'cancelled',
                                        'bg-amber-100 text-amber-700': !a.status || a.status === 'waiting',
                                    }">{{ a.status || 'waiting' }}</span>
                                </td>
                            </tr>
                            <tr v-if="!appointments?.data?.length">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No appointments found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="appointments?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-medium">{{ appointments.from || 0 }}</span>
                        to <span class="font-medium">{{ appointments.to || 0 }}</span>
                        of <span class="font-medium">{{ appointments.total }}</span> results
                    </div>
                    <Pagination :links="appointments.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>