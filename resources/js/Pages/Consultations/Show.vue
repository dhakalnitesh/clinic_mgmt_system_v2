<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import { computed } from 'vue'

const props = defineProps({
    consultation: Object,
})

const uploadedFiles = computed(() => {
    if (!props.consultation?.document_path) return []
    try {
        const parsed = JSON.parse(props.consultation.document_path)
        return Array.isArray(parsed) ? parsed : []
    } catch {
        return []
    }
})

const statusClass = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-700',
        in_progress: 'bg-blue-100 text-blue-700',
        completed: 'bg-emerald-100 text-emerald-700',
        review_pending: 'bg-amber-100 text-amber-700',
        signed: 'bg-purple-100 text-purple-700',
        cancelled: 'bg-red-100 text-red-700',
    }
    return map[status] || 'bg-gray-100 text-gray-700'
}
</script>

<template>
    <Head :title="'Consultation #' + consultation.id" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-start">
                <div>
                    <Link :href="route('consultations.index')" class="text-sm text-indigo-600 hover:underline flex items-center gap-1 mb-2">
                        <i class="fas fa-arrow-left"></i> Back to Consultations
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-stethoscope text-indigo-600"></i>
                        Consultation #{{ consultation.id }}
                    </h1>
                </div>
                <span class="rounded-full px-4 py-1.5 text-sm font-semibold" :class="statusClass(consultation.consultation_status)">
                    {{ consultation.consultation_status?.replace('_', ' ') || 'N/A' }}
                </span>
            </div>

            <!-- Patient & Doctor Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-user text-indigo-600"></i> Patient
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name</span>
                            <span class="font-medium">{{ consultation.patient?.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">UHID</span>
                            <span class="font-medium">{{ consultation.patient?.uhid }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone</span>
                            <span class="font-medium">{{ consultation.patient?.phone }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-md text-indigo-600"></i> Doctor & Visit
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Doctor</span>
                            <span class="font-medium">{{ consultation.doctor?.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Consulted At (AD)</span>
                            <span class="font-medium">{{ consultation.consulted_at ? new Date(consultation.consulted_at).toLocaleString() : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Created (BS)</span>
                            <span class="font-medium">{{ consultation.created_at_bs || '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Details -->
            <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Clinical Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-600 mb-2">Chief Complaint</h3>
                        <p class="text-gray-900">{{ consultation.chief_complaint || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600 mb-2">History</h3>
                        <p class="text-gray-900">{{ consultation.history || 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-medium text-gray-600 mb-2">Examination Notes</h3>
                        <p class="text-gray-900">{{ consultation.examination_notes || 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-medium text-gray-600 mb-2">Diagnosis</h3>
                        <p class="text-gray-900">{{ consultation.diagnosis || 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-medium text-gray-600 mb-2">Notes</h3>
                        <p class="text-gray-900">{{ consultation.notes || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Vitals -->
            <div v-if="consultation.vitals" class="bg-white rounded-xl shadow border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Vitals</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-if="consultation.vitals.blood_pressure" class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-indigo-600">{{ consultation.vitals.blood_pressure }}</div>
                        <div class="text-xs text-gray-600">BP (mmHg)</div>
                    </div>
                    <div v-if="consultation.vitals.pulse" class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-indigo-600">{{ consultation.vitals.pulse }}</div>
                        <div class="text-xs text-gray-600">Pulse (bpm)</div>
                    </div>
                    <div v-if="consultation.vitals.temperature" class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-indigo-600">{{ consultation.vitals.temperature }}</div>
                        <div class="text-xs text-gray-600">Temp (°F)</div>
                    </div>
                    <div v-if="consultation.vitals.oxygen" class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-indigo-600">{{ consultation.vitals.oxygen }}%</div>
                        <div class="text-xs text-gray-600">O2 Sat</div>
                    </div>
                </div>
            </div>

            <!-- Prescriptions -->
            <div v-if="consultation.prescriptions?.length" class="bg-white rounded-xl shadow border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Prescriptions</h2>
                <div v-for="prescription in consultation.prescriptions" :key="prescription.id" class="mb-4 last:mb-0">
                    <div class="text-sm text-gray-600 mb-2">Prescribed: {{ prescription.prescribed_at ?? '-' }} <span v-if="prescription.created_at_bs" class="text-gray-400">(BS: {{ prescription.created_at_bs }})</span></div>
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Medicine</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Dosage</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Frequency</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Duration</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Instructions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in prescription.items" :key="item.id">
                                <td class="px-4 py-3 text-sm font-medium">{{ item.medicine_name || 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.dosage || '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.frequency || '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.duration || '-' }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.instructions || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Follow Up -->
            <div v-if="consultation.follow_up_date" class="bg-white rounded-xl shadow border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Follow Up</h2>
                <p class="text-gray-700">Follow-up date: <span class="font-medium">{{ new Date(consultation.follow_up_date).toLocaleDateString() }}</span></p>
            </div>

            <!-- Uploaded Files -->
            <div v-if="uploadedFiles.length" class="bg-white rounded-xl shadow border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-paperclip text-indigo-600"></i> Attachments
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <a
                        v-for="(file, index) in uploadedFiles"
                        :key="index"
                        :href="'/storage/' + file"
                        target="_blank"
                        class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 hover:border-indigo-400 hover:bg-indigo-50 transition"
                    >
                        <i class="fas fa-file text-indigo-600 text-xl"></i>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-gray-900">
                                {{ file.split('/').pop() }}
                            </div>
                            <div class="text-xs text-gray-500">Click to open</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
