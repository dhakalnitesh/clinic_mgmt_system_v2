<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    patient: Object,
    consultations: Array,
    prescriptions: Array,
    labOrders: Array,
    followUps: Array,
})

const activeTab = ref('visits')

const tabs = [
    { key: 'visits', label: 'Visits', icon: 'fa-notes-medical' },
    { key: 'consultations', label: 'Consultations', icon: 'fa-stethoscope' },
    { key: 'prescriptions', label: 'Prescriptions', icon: 'fa-prescription' },
    { key: 'lab', label: 'Lab Orders', icon: 'fa-flask' },
    { key: 'followups', label: 'Follow-ups', icon: 'fa-calendar-alt' },
]

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-'
</script>

<template>
    <Head :title="'Patient History - ' + (patient?.name || '')" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('patients.index')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-hospital-user text-indigo-600"></i>
                            Patient History
                        </h1>
                        <p class="text-sm text-gray-600">Complete medical history and records</p>
                    </div>
                </div>
            </div>

            <!-- Patient Info Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <div class="h-16 w-16 rounded-2xl bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-700">
                        {{ patient?.name?.charAt(0) || 'P' }}
                    </div>
                    <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">Name</div>
                            <div class="text-lg font-semibold text-gray-900">{{ patient.name }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">UHID</div>
                            <div class="text-lg font-semibold text-gray-900">{{ patient.uhid }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">Phone</div>
                            <div class="text-lg font-semibold text-gray-900">{{ patient.phone }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-500">Gender / Age</div>
                            <div class="text-lg font-semibold text-gray-900">{{ patient.gender || '-' }} / {{ patient.age || '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 overflow-x-auto rounded-xl border border-gray-200 bg-white p-2 shadow-sm">
                <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                    class="flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition"
                    :class="activeTab === tab.key ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
                    <i :class="'fas ' + tab.icon"></i> {{ tab.label }}
                </button>
            </div>

            <!-- Visits -->
            <div v-if="activeTab === 'visits'" class="space-y-3">
                <div v-for="v in patient?.visits || []" :key="v.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-gray-900">{{ formatDate(v.visited_at) }}</div>
                            <div class="text-sm text-gray-600 mt-1">Doctor: Dr. {{ v.doctor?.name || 'N/A' }}</div>
                            <div class="text-sm text-gray-600">Complaint: {{ v.chief_complaint || '-' }}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="v.status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                            {{ v.status || 'N/A' }}
                        </span>
                    </div>
                </div>
                <div v-if="!patient?.visits?.length" class="text-center text-gray-500 py-8">No visits recorded.</div>
            </div>

            <!-- Consultations -->
            <div v-if="activeTab === 'consultations'" class="space-y-3">
                <div v-for="c in consultations" :key="c.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-gray-900">{{ formatDate(c.created_at) }}</div>
                            <div class="text-sm text-gray-600 mt-1">Doctor: Dr. {{ c.doctor?.name || 'N/A' }}</div>
                            <div v-if="c.diagnosis" class="text-sm text-gray-600">Diagnosis: {{ c.diagnosis }}</div>
                            <div v-if="c.chief_complaint" class="text-sm text-gray-600">Complaint: {{ c.chief_complaint }}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            {{ c.consultation_status || 'completed' }}
                        </span>
                    </div>
                </div>
                <div v-if="!consultations?.length" class="text-center text-gray-500 py-8">No consultations recorded.</div>
            </div>

            <!-- Prescriptions -->
            <div v-if="activeTab === 'prescriptions'" class="space-y-3">
                <div v-for="p in prescriptions" :key="p.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="font-semibold text-gray-900">{{ formatDate(p.created_at) }}</div>
                    <div class="text-sm text-gray-600">Doctor: Dr. {{ p.consultation?.doctor?.name || 'N/A' }}</div>
                    <div v-if="p.advices" class="mt-2 text-sm text-gray-700">Advice: {{ p.advices }}</div>
                    <div v-if="p.items?.length" class="mt-3">
                        <div class="text-xs font-semibold uppercase text-gray-500 mb-1">Medicines</div>
                        <div v-for="item in p.items" :key="item.id" class="text-sm text-gray-700 flex gap-4">
                            <span class="font-medium">{{ item.medicine_name || item.medicine_id }}</span>
                            <span>{{ item.dosage || '' }}</span>
                            <span>{{ item.frequency || '' }}</span>
                            <span>{{ item.duration || '' }}</span>
                        </div>
                    </div>
                </div>
                <div v-if="!prescriptions?.length" class="text-center text-gray-500 py-8">No prescriptions recorded.</div>
            </div>

            <!-- Lab Orders -->
            <div v-if="activeTab === 'lab'" class="space-y-3">
                <div v-for="lo in labOrders" :key="lo.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-gray-900">{{ formatDate(lo.created_at) }}</div>
                            <div class="text-sm text-gray-600">Doctor: Dr. {{ lo.doctor?.name || 'N/A' }}</div>
                            <div v-if="lo.items?.length" class="mt-2">
                                <div v-for="item in lo.items" :key="item.id" class="text-sm text-gray-700">
                                    {{ item.lab_test?.name || item.test_name || 'Test' }}
                                </div>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="{
                            'bg-emerald-100 text-emerald-700': lo.status === 'completed',
                            'bg-amber-100 text-amber-700': lo.status === 'pending' || !lo.status,
                        }">{{ lo.status || 'pending' }}</span>
                    </div>
                </div>
                <div v-if="!labOrders?.length" class="text-center text-gray-500 py-8">No lab orders recorded.</div>
            </div>

            <!-- Follow-ups -->
            <div v-if="activeTab === 'followups'" class="space-y-3">
                <div v-for="fu in followUps" :key="fu.id" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-gray-900">{{ fu.follow_up_date }}</div>
                            <div class="text-sm text-gray-600">Doctor: Dr. {{ fu.doctor?.name || 'N/A' }}</div>
                            <div v-if="fu.notes" class="text-sm text-gray-600 mt-1">{{ fu.notes }}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="{
                            'bg-emerald-100 text-emerald-700': fu.status === 'completed',
                            'bg-red-100 text-red-700': fu.status === 'cancelled',
                            'bg-amber-100 text-amber-700': fu.status === 'pending',
                        }">{{ fu.status }}</span>
                    </div>
                </div>
                <div v-if="!followUps?.length" class="text-center text-gray-500 py-8">No follow-ups recorded.</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>