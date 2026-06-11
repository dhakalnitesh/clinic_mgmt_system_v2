<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    consultations: Object,
    filters: Object,
})
</script>

<template>
    <Head title="Consultations" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-stethoscope text-indigo-600"></i>
                        Consultations
                    </h1>
                    <p class="text-sm text-gray-600">Manage all patient consultations efficiently</p>
                </div>
            </div>

            <FilterBar
                route-name="consultations.index"
                :filters="filters"
                search-placeholder="Patient name, UHID, phone..."
            />

            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Patient</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">UHID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Doctor</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Date</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(c, index) in consultations?.data || []" :key="c.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm">{{ ((consultations.current_page - 1) * consultations.per_page) + index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center font-bold text-indigo-700">
                                            {{ c.patient?.name?.charAt(0) || 'P' }}
                                        </div>
                                        <div>
                                            <div class="font-semibold">{{ c.patient?.name }}</div>
                                            <div class="text-xs text-gray-500">{{ c.patient?.phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ c.patient?.uhid }}</td>
                                <td class="px-6 py-4 text-sm">{{ c.doctor?.name }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="{
                                        'bg-emerald-100 text-emerald-700': c.consultation_status === 'completed',
                                        'bg-amber-100 text-amber-700': c.consultation_status === 'pending',
                                        'bg-blue-100 text-blue-700': c.consultation_status === 'active' || c.consultation_status === 'draft',
                                    }">
                                        {{ c.consultation_status || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ c.created_at ? new Date(c.created_at).toLocaleDateString() : '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="router.visit(route('consultations.show', c.id))"
                                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!consultations?.data?.length">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No consultations found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="consultations?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t px-6 py-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ consultations.from || 0 }}</span>
                        to <span class="font-medium">{{ consultations.to || 0 }}</span>
                        of <span class="font-medium">{{ consultations.total }}</span> results
                    </div>
                    <Pagination :links="consultations.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>