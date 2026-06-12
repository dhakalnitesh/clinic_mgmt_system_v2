<template>
    <AuthenticatedLayout>
        <Head title="Doctor Details" />

        <div class="p-6 space-y-6">

            <!-- Back + Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('doctors.index')"
                        class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <i class="fas fa-user-md text-teal-600"></i>
                            Doctor Details
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Complete profile and activity history</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button @click="openEdit"
                        class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- LEFT: Doctor Profile Card -->
                <div class="lg:col-span-1 space-y-6">

                    <!-- Photo & Basic Info Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-teal-600 to-teal-700 h-24"></div>

                        <div class="px-6 pb-6 -mt-12 text-center">
                            <div class="mx-auto size-24 rounded-full border-4 border-white dark:border-gray-800 overflow-hidden bg-white shadow-lg">
                                <img v-if="doctor.photo_url" :src="doctor.photo_url"
                                    :alt="doctor.name" class="size-full object-cover" />
                                <div v-else class="size-full bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center">
                                    <i class="fas fa-user-md text-3xl text-teal-600 dark:text-teal-400"></i>
                                </div>
                            </div>

                            <h2 class="mt-3 text-xl font-bold text-gray-900 dark:text-gray-100">{{ doctor.name }}</h2>
                            <p class="text-sm text-teal-600 dark:text-teal-400 font-medium">{{ doctor.specialization || 'General Practitioner' }}</p>

                            <div class="mt-4 flex justify-center gap-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    NMC: {{ doctor.nmc_number }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <i class="fas fa-address-card text-teal-600"></i> Contact Info
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Phone</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ doctor.phone || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Address</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ doctor.address1 || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Consultation Fee</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">Rs. {{ doctor.consultation_fee ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Registered</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ doctor.created_at_bs || '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Availability Schedule -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-teal-600"></i> Availability Schedule
                        </h3>

                        <div v-if="doctor.schedules?.length" class="space-y-2">
                            <div v-for="sched in doctor.schedules" :key="sched.id"
                                class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ sched.day }}</span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ sched.start_time || '—' }} - {{ sched.end_time || '—' }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">No schedule set</p>
                    </div>

                    <!-- Notes -->
                    <div v-if="doctor.notes" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-3">
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-teal-600"></i> Notes
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ doctor.notes }}</p>
                    </div>

                </div>

                <!-- RIGHT: Activity History -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Stats Mini Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
                            <p class="text-2xl font-bold text-teal-600">{{ stats.consultations }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Consultations</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
                            <p class="text-2xl font-bold text-indigo-600">{{ stats.prescriptions }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Prescriptions</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
                            <p class="text-2xl font-bold text-purple-600">{{ stats.labOrders }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lab Tests</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
                            <p class="text-2xl font-bold text-rose-600">{{ stats.patients }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Patients</p>
                        </div>
                    </div>

                    <!-- Activity Tabs -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex overflow-x-auto">
                                <button v-for="tab in tabs" :key="tab.key"
                                    @click="activeTab = tab.key"
                                    :class="[
                                        activeTab === tab.key
                                            ? 'border-teal-500 text-teal-600 dark:text-teal-400'
                                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                                        'px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap'
                                    ]">
                                    <i :class="[tab.icon, 'mr-1.5']"></i>
                                    {{ tab.label }}
                                    <span v-if="tab.count" class="ml-1 text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded-full">{{ tab.count }}</span>
                                </button>
                            </nav>
                        </div>

                        <div class="p-6">
                            <!-- Consultations Tab -->
                            <div v-if="activeTab === 'consultations'">
                                <div v-if="doctor.consultations?.length" class="space-y-3">
                                    <div v-for="c in doctor.consultations" :key="c.id"
                                        class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="size-10 rounded-full bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-stethoscope text-teal-600 dark:text-teal-400 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ c.patient?.name || 'Unknown Patient' }}
                                                </p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ c.created_at_bs }}</span>
                                            </div>
                                            <p v-if="c.diagnosis" class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ c.diagnosis }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                    :class="c.consultation_status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'">
                                                    {{ c.consultation_status || 'pending' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic text-center py-8">No consultations yet</p>
                            </div>

                            <!-- Prescriptions Tab -->
                            <div v-if="activeTab === 'prescriptions'">
                                <div v-if="doctor.prescriptions?.length" class="space-y-3">
                                    <div v-for="p in doctor.prescriptions" :key="p.id"
                                        class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="size-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-prescription text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ p.patient?.name || 'Unknown Patient' }}
                                                </p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ p.created_at_bs }}</span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                RX: {{ p.prescription_number || '—' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                    :class="{
                                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': p.status === 'dispensed',
                                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300': p.status === 'pending' || p.status === 'partial',
                                                        'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': !p.status
                                                    }">
                                                    {{ p.status || 'pending' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic text-center py-8">No prescriptions yet</p>
                            </div>

                            <!-- Lab Orders Tab -->
                            <div v-if="activeTab === 'lab_orders'">
                                <div v-if="doctor.labOrders?.length" class="space-y-3">
                                    <div v-for="lab in doctor.labOrders" :key="lab.id"
                                        class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="size-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-flask text-purple-600 dark:text-purple-400 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ lab.patient?.name || 'Unknown Patient' }}
                                                </p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ lab.created_at_bs }}</span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                Order: {{ lab.order_number || '—' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                    :class="{
                                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': lab.status === 'completed',
                                                        'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300': lab.status === 'processing',
                                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300': lab.status === 'ordered',
                                                        'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': !lab.status
                                                    }">
                                                    {{ lab.status || 'pending' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic text-center py-8">No lab orders yet</p>
                            </div>

                            <!-- Follow-ups Tab -->
                            <div v-if="activeTab === 'follow_ups'">
                                <div v-if="doctor.followUps?.length" class="space-y-3">
                                    <div v-for="f in doctor.followUps" :key="f.id"
                                        class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="size-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-calendar-check text-rose-600 dark:text-rose-400 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ f.patient?.name || 'Unknown Patient' }}
                                                </p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ f.follow_up_date_bs || f.follow_up_date }}</span>
                                            </div>
                                            <p v-if="f.notes" class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ f.notes }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                    :class="{
                                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': f.status === 'completed',
                                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300': f.status === 'pending',
                                                        'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300': f.status === 'overdue',
                                                        'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': !f.status
                                                    }">
                                                    {{ f.status || 'pending' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic text-center py-8">No follow-ups yet</p>
                            </div>

                            <!-- Visits Tab -->
                            <div v-if="activeTab === 'visits'">
                                <div v-if="doctor.visits?.length" class="space-y-3">
                                    <div v-for="v in doctor.visits" :key="v.id"
                                        class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="size-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-person-walking text-blue-600 dark:text-blue-400 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ v.patient?.name || 'Unknown Patient' }}
                                                </p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ v.created_at_bs }}</span>
                                            </div>
                                            <p v-if="v.chief_complaint" class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ v.chief_complaint }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                    :class="{
                                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': v.status === 'completed',
                                                        'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300': v.status === 'in_consultation',
                                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300': v.status === 'waiting' || v.status === 'vitals_pending',
                                                        'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300': v.status === 'cancelled'
                                                    }">
                                                    {{ v.status?.replace('_', ' ') || 'waiting' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic text-center py-8">No visits yet</p>
                            </div>

                            <!-- Appointments Tab -->
                            <div v-if="activeTab === 'appointments'">
                                <div v-if="doctor.appointments?.length" class="space-y-3">
                                    <div v-for="a in doctor.appointments" :key="a.id"
                                        class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <div class="size-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-calendar-alt text-amber-600 dark:text-amber-400 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ a.patient?.name || 'Unknown Patient' }}
                                                </p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ a.appointment_date }}</span>
                                            </div>
                                            <p v-if="a.reason" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ a.reason }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs px-2 py-0.5 rounded-full"
                                                    :class="{
                                                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': a.status === 'confirmed' || a.status === 'visited',
                                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300': a.status === 'pending',
                                                        'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300': a.status === 'cancelled',
                                                        'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400': !a.status
                                                    }">
                                                    {{ a.status || 'pending' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic text-center py-8">No appointments yet</p>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- Edit Modal -->
        <EditDoctorModal v-if="showEditModal" :doctor="doctor" @close="showEditModal = false" @success="refreshDoctor" />

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import EditDoctorModal from './EditModal.vue'

const props = defineProps({
    doctor: { type: Object, required: true }
})

const showEditModal = ref(false)

const openEdit = () => {
    showEditModal.value = true
}

const refreshDoctor = () => {
    router.reload({ only: ['doctor'] })
    showEditModal.value = false
}

const stats = computed(() => ({
    consultations: props.doctor.consultations?.length ?? 0,
    prescriptions: props.doctor.prescriptions?.length ?? 0,
    labOrders: props.doctor.labOrders?.length ?? 0,
    patients: new Set([
        ...(props.doctor.consultations?.map(c => c.patient_id) ?? []),
        ...(props.doctor.prescriptions?.map(p => p.patient_id) ?? []),
        ...(props.doctor.visits?.map(v => v.patient_id) ?? []),
    ]).size,
}))

const activeTab = ref('consultations')

const tabs = computed(() => [
    { key: 'consultations', label: 'Consultations', icon: 'fas fa-stethoscope', count: stats.value.consultations },
    { key: 'prescriptions', label: 'Prescriptions', icon: 'fas fa-prescription', count: stats.value.prescriptions },
    { key: 'lab_orders', label: 'Lab Tests', icon: 'fas fa-flask', count: stats.value.labOrders },
    { key: 'follow_ups', label: 'Follow-Ups', icon: 'fas fa-calendar-check', count: props.doctor.followUps?.length ?? 0 },
    { key: 'visits', label: 'Visits', icon: 'fas fa-person-walking', count: props.doctor.visits?.length ?? 0 },
    { key: 'appointments', label: 'Appointments', icon: 'fas fa-calendar-alt', count: props.doctor.appointments?.length ?? 0 },
])
</script>
