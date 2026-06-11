<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    followUps: Object,
    filters: Object,
})

const confirmComplete = ref(null)
const confirmCancel = ref(null)
const completeNotes = ref('')

const statusClass = (status) => {
    switch (status) {
        case 'completed': return 'bg-emerald-100 text-emerald-700'
        case 'cancelled': return 'bg-red-100 text-red-700'
        default: return 'bg-amber-100 text-amber-700'
    }
}

const markComplete = () => {
    if (!confirmComplete.value) return
    router.patch(route('follow-ups.complete', confirmComplete.value.id), {
        completed_notes: completeNotes.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            confirmComplete.value = null
            completeNotes.value = ''
        }
    })
}

const markCancelled = () => {
    if (!confirmCancel.value) return
    router.patch(route('follow-ups.cancel', confirmCancel.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => { confirmCancel.value = null }
    })
}
</script>

<template>
    <Head title="Follow-ups" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-indigo-600"></i>
                        Follow-ups
                    </h1>
                    <p class="text-sm text-gray-600">Track and manage patient follow-up visits</p>
                </div>
            </div>

            <FilterBar
                route-name="follow-ups.index"
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
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Doctor</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Follow-up Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Created (BS)</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Notes</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(fu, index) in followUps?.data || []" :key="fu.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm">{{ ((followUps.current_page - 1) * followUps.per_page) + index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ fu.patient?.name || 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ fu.patient?.phone || '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">Dr. {{ fu.doctor?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm font-mono">{{ fu.follow_up_date_bs || fu.follow_up_date }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ fu.created_at_bs || '-' }}</td>
                                <td class="px-6 py-4 text-sm max-w-xs truncate">{{ fu.notes || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusClass(fu.status)">
                                        {{ fu.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('patients.show', fu.patient_id)"
                                              class="text-indigo-600 hover:text-indigo-800" title="Patient History">
                                            <i class="fas fa-history"></i>
                                        </Link>
                                        <button v-if="fu.status === 'pending'"
                                            @click="confirmComplete = fu"
                                            class="text-emerald-600 hover:text-emerald-800" title="Mark Completed">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button v-if="fu.status === 'pending'"
                                            @click="confirmCancel = fu"
                                            class="text-red-600 hover:text-red-800" title="Cancel">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!followUps?.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No follow-ups found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="followUps?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t px-6 py-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ followUps.from || 0 }}</span>
                        to <span class="font-medium">{{ followUps.to || 0 }}</span>
                        of <span class="font-medium">{{ followUps.total }}</span> results
                    </div>
                    <Pagination :links="followUps.links" />
                </div>
            </div>

            <!-- Complete Modal -->
            <div v-if="confirmComplete" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                    <h2 class="text-lg font-semibold text-gray-800">Mark Follow-up Completed</h2>
                    <p class="text-sm text-gray-600 mt-2">Patient: <span class="font-semibold">{{ confirmComplete.patient?.name }}</span></p>
                    <textarea v-model="completeNotes" rows="3" placeholder="Completion notes..."
                        class="mt-4 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="confirmComplete = null; completeNotes = ''"
                            class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-100">Cancel</button>
                        <button @click="markComplete"
                            class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Mark Completed</button>
                    </div>
                </div>
            </div>

            <!-- Cancel Modal -->
            <div v-if="confirmCancel" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                    <h2 class="text-lg font-semibold text-gray-800">Cancel Follow-up</h2>
                    <p class="text-sm text-gray-600 mt-2">Are you sure you want to cancel this follow-up for <span class="font-semibold">{{ confirmCancel.patient?.name }}</span>?</p>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="confirmCancel = null"
                            class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-100">No</button>
                        <button @click="markCancelled"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Yes, Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>