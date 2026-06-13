<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
    patients: Object,
    filters: Object,
})
</script>

<template>
    <Head title="Patient Report" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('reports.index')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-xl"></i></Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Patient Report</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Overview of all registered patients</p>
                    </div>
                </div>
                <a :href="route('reports.patients.export')"
                    class="text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
            </div>

            <FilterBar route-name="reports.patients" :filters="filters" search-placeholder="Patient name, phone, UHID..." />

            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">UHID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Phone</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Gender</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Age</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Visits</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(p, i) in patients?.data || []" :key="p.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm">{{ ((patients.current_page - 1) * patients.per_page) + i + 1 }}</td>
                                <td class="px-6 py-4 font-mono text-sm">{{ p.uhid || '-' }}</td>
                                <td class="px-6 py-4 font-medium">{{ p.name }}</td>
                                <td class="px-6 py-4 text-sm">{{ p.phone }}</td>
                                <td class="px-6 py-4 text-sm">{{ p.gender || '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ p.age || '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ p.visits_count || 0 }}</td>
                                <td class="px-6 py-4 text-sm">{{ p.created_at ? new Date(p.created_at).toLocaleDateString() : '-' }}</td>
                            </tr>
                            <tr v-if="!patients?.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No patients found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="patients?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-medium">{{ patients.from || 0 }}</span>
                        to <span class="font-medium">{{ patients.to || 0 }}</span>
                        of <span class="font-medium">{{ patients.total }}</span> results
                    </div>
                    <Pagination :links="patients.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>