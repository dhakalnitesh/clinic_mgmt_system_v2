<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps({
    selectedPatient: {
        type: Object,
        default: () => ({}),
    },
    visit: {
        type: Object,
        default: () => ({}),
    },
    vitals: {
        type: Object,
        default: () => ({}),
    },
    recentMedicines: {
        type: Array,
        default: () => [],
    },
    pendingTests: {
        type: Array,
        default: () => [],
    },
    history: {
        type: Array,
        default: () => [],
    },
    availableTests: {
        type: Array,
        default: () => ([
            'CBC',
            'Blood Sugar',
            'HbA1c',
            'Urine R/M',
            'LFT',
            'KFT',
            'ECG',
            'X-Ray Chest',
            'USG Abdomen',
            'Dengue NS1',
        ]),
    },
})

const activeTab = ref('overview')

const tabs = [
    { key: 'overview', label: 'Overview' },
    { key: 'vitals', label: 'Vitals' },
    { key: 'consultation', label: 'Consultation' },
    { key: 'prescription', label: 'Prescription' },
    { key: 'labs', label: 'Lab & Tests' },
    { key: 'files', label: 'Files' },
    { key: 'history', label: 'History' },
]

const form = useForm({
    patient_id: '',
    appointment_id: '',
    visit_id: '',

    blood_pressure: '',
    pulse: '',
    temperature: '',
    oxygen: '',
    height: '',
    weight: '',

    chief_complaint: '',
    examination_notes: '',
    advice: '',
    follow_up_date: '',
    notes: '',
    diagnosis: [],
    
    medicines: [
      {
        medicine_id: '',
        medicine_name: '',
        dosage: '',
        frequency: '',
        duration: '',
        quantity:'',
        instruction: '',
      },
    ],
    
    symptoms: [],
    tests: [],
    lab_notes: '',
    files: [],
    created_by: '',
doctor_id: '',
consultation_status: 'draft',
})

const symptomsList = [
    'Fever',
    'Headache',
    'Cough',
    'Vomiting',
    'Body Pain',
    'Weakness',
    'Chest Pain',
    'Cold',
    'Shortness of Breath',
    'Nausea',
]

const addMedicine = () => {
    form.medicines.push({
        medicine_id: '',
        medicine_name: '',
        dosage: '',
        frequency: '',
        duration: '',
        quantity: '',
        instruction: '',
    })
}

const removeMedicine = (index) => {
    form.medicines.splice(index, 1)
}

const toggleTest = (testName) => {
    const index = form.tests.indexOf(testName)
    if (index === -1) {
        form.tests.push(testName)
    } else {
        form.tests.splice(index, 1)
    }
}

const handleFileChange = (event) => {
    const files = Array.from(event.target.files || [])
    form.files = files
}

const removeUploadedFile = (index) => {
    form.files.splice(index, 1)
}

const submit = () => {
    // console.log("i am working la",form)
        console.log(form.doctor_id)
    form.post(route('consultations.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset()
        }
    })
}

watch(
    () => [props.selectedPatient, props.visit, props.vitals],
    () => {
        const patient = props.selectedPatient || {}
        const visit = props.visit || {}
        const vitals = props.vitals || {}

        form.patient_id = patient.id ?? ''
        form.appointment_id = visit.appointment_id ?? ''
        form.visit_id = visit.id ?? ''

        form.blood_pressure = vitals.blood_pressure ?? ''
        form.pulse = vitals.pulse ?? ''
        form.temperature = vitals.temperature ?? ''
        form.oxygen = vitals.oxygen ?? ''
        form.height = vitals.height ?? ''
        form.weight = vitals.weight ?? ''

        form.chief_complaint = visit.chief_complaint ?? ''
        form.symptoms = Array.isArray(visit.symptoms) ? [...visit.symptoms] : []
        form.examination_notes = visit.examination_notes ?? ''
        form.diagnosis = visit.diagnosis ?? ''
    //     form.diagnosis = Array.isArray(visit.diagnosis)
    // ? [...visit.diagnosis]
    // : []
        form.advice = visit.advice ?? ''
        form.follow_up_date = visit.follow_up_date ?? ''
        form.notes = visit.notes ?? ''

        form.medicines = Array.isArray(visit.medicines) && visit.medicines.length
            ? visit.medicines.map((m) => ({
                medicine_id: m.medicine_id ?? '',
                dosage: m.dosage ?? '',
                frequency: m.frequency ?? '',
                duration: m.duration ?? '',
                instruction: m.instruction ?? '',
            }))
            : [
                {
                    medicine_id: '',
                    dosage: '',
                    frequency: '',
                    duration: '',
                    quantity:'',
                    instruction: '',
                },
            ]
form.doctor_id = visit.doctor_id ?? ''
        form.tests = Array.isArray(visit.tests) ? [...visit.tests] : []
        form.lab_notes = visit.lab_notes ?? ''
        form.files = []
    },
    { immediate: true, deep: true }
)

const patientInitials = computed(() => {
    const name = props.selectedPatient?.name || ''
    if (!name.trim()) return '??'
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(word => word[0]?.toUpperCase() || '')
        .join('')
})

const patientName = computed(() => props.selectedPatient?.name || 'No Patient Selected')
const patientUhid = computed(() => props.selectedPatient?.uhid || '-')
const patientGender = computed(() => props.selectedPatient?.gender || '-')
const patientAge = computed(() => props.selectedPatient?.age || '-')
const visitType = computed(() => props.visit?.type || 'Visit')
const doctorName = computed(() => props.visit?.doctor_name || props.selectedPatient?.doctor_name || '-')
const visitStatus = computed(() => props.visit?.status || 'Active Visit')
const paymentStatus = computed(() => props.visit?.payment_status || 'Cash Pending')

const safeText = (value, fallback = '-') => {
    if (value === null || value === undefined || value === '') return fallback
    return value
}
</script>

<template>
    <Head title="Create Consultation" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="mx-auto max-w-7xl space-y-6 p-6">

                <!-- ===================================================== -->
                <!-- PAGE HEADER -->
                <!-- ===================================================== -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Create Consultation
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Manage patient consultation, vitals, prescription, lab tests, follow-up and notes.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('consultations.index')"
                            class="rounded-2xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100"
                        >
                            Back
                        </Link>

                        <button
                            @click="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Save Consultation
                        </button>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- PATIENT HEADER -->
                <!-- ===================================================== -->
                <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-6 p-6 lg:flex-row lg:items-center lg:justify-between">

                        <div class="flex items-start gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100 text-xl font-bold text-indigo-700">
                                {{ patientInitials }}
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-2xl font-bold text-gray-900">
                                        {{ patientName }}
                                    </h2>

                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ visitStatus }}
                                    </span>

                                    <!-- <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        {{ paymentStatus }}
                                    </span> -->
                                </div>

                                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
                                    <div>
                                        <span class="font-semibold text-gray-800">UHID:</span>
                                        {{ patientUhid }}
                                    </div>

                                    <div>
                                        <span class="font-semibold text-gray-800">Gender:</span>
                                        {{ patientGender }}
                                    </div>

                                    <div>
                                        <span class="font-semibold text-gray-800">Age:</span>
                                        {{ safeText(patientAge) }}
                                    </div>

                                    <!-- <div>
                                        <span class="font-semibold text-gray-800">Visit:</span>
                                        {{ visitType }}
                                    </div> -->

                                    <div>
                                        <span class="font-semibold text-gray-800">Doctor:</span>
                                        {{ doctorName }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    BP
                                </div>
                                <div class="mt-2 text-lg font-bold text-gray-900">
                                    {{ safeText(vitals?.blood_pressure) }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Pulse
                                </div>
                                <div class="mt-2 text-lg font-bold text-gray-900">
                                    {{ safeText(vitals?.pulse) }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Temp
                                </div>
                                <div class="mt-2 text-lg font-bold text-gray-900">
                                    {{ safeText(vitals?.temperature) }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    SPO2
                                </div>
                                <div class="mt-2 text-lg font-bold text-gray-900">
                                    {{ safeText(vitals?.oxygen) }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Weight
                                </div>
                                <div class="mt-2 text-lg font-bold text-gray-900">
                                    {{ safeText(vitals?.weight) }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-red-500">
                                    Allergy
                                </div>
                                <div class="mt-2 text-lg font-bold text-red-700">
                                    {{ safeText(selectedPatient?.allergy, 'None') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- TABS -->
                <!-- ===================================================== -->
                <div class="overflow-x-auto rounded-3xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex min-w-max items-center gap-2 p-4">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            class="rounded-2xl px-5 py-3 text-sm font-semibold transition"
                            :class="activeTab === tab.key
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100'"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- OVERVIEW -->
                <!-- ===================================================== -->
                <div v-if="activeTab === 'overview'" class="grid gap-6 xl:grid-cols-3">

                    <div class="space-y-6 xl:col-span-2">

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Chief Complaint
                                </h2>
                            </div>

                            <div class="p-6">
                                <textarea
                                    v-model="form.chief_complaint"
                                    rows="4"
                                    placeholder="Enter patient's complaint..."
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-100"
                                />
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Symptoms
                                </h2>
                            </div>

                            <div class="grid gap-4 p-6 md:grid-cols-2">
                                <label
                                    v-for="symptom in symptomsList"
                                    :key="symptom"
                                    class="flex items-center gap-3 rounded-2xl border border-gray-200 p-4 transition hover:bg-gray-50"
                                >
                                    <input
                                        v-model="form.symptoms"
                                        :value="symptom"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600"
                                    >

                                    <span class="text-sm font-medium text-gray-700">
                                        {{ symptom }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Diagnosis
                                </h2>
                            </div>

                            <div class="p-6">
                                <textarea
                                    v-model="form.diagnosis"
                                    rows="5"
                                    placeholder="Enter diagnosis..."
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-100"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Recent Medicines
                                </h2>
                            </div>

                            <div class="space-y-4 p-6">
                                <div
                                    v-for="medicine in recentMedicines"
                                    :key="medicine.id ?? medicine.name ?? medicine.medicine_name"
                                    class="rounded-2xl border border-gray-200 p-4"
                                >
                                    <div class="font-semibold text-gray-900">
                                        {{ medicine.name ?? medicine.medicine_name ?? medicine.title ?? 'Medicine' }}
                                    </div>

                                       <div class="mt-1 text-sm text-gray-500">
                                        {{ medicine.quantity ?? medicine.total_quantity ?? medicine.total ?? 'No quantity details' }}
                                    </div>

                                    <div class="mt-1 text-sm text-gray-500">
                                        {{ medicine.dosage ?? medicine.instruction ?? medicine.frequency ?? 'No dosage details' }}
                                    </div>

                                   
                                </div>

                                <div v-if="!recentMedicines.length" class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-400">
                                    No recent medicines found.
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Pending Lab Tests
                                </h2>
                            </div>

                            <div class="space-y-3 p-6">
                                <div
                                    v-for="test in pendingTests"
                                    :key="test.id ?? test.name ?? test.title"
                                    class="rounded-2xl bg-amber-50 p-4 text-sm font-medium text-amber-700"
                                >
                                    {{ test.name ?? test.title ?? test.test_name ?? 'Lab test' }}
                                </div>

                                <div v-if="!pendingTests.length" class="rounded-2xl bg-gray-50 p-4 text-sm font-medium text-gray-500">
                                    No pending tests.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- VITALS -->
                <!-- ===================================================== -->
                <div v-if="activeTab === 'vitals'">
                    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-6 py-5">
                            <h2 class="text-lg font-bold text-gray-900">
                                Patient Vitals
                            </h2>
                        </div>

                        <div class="grid gap-6 p-6 md:grid-cols-2 xl:grid-cols-3">

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Blood Pressure
                                </label>

                                <input
                                    v-model="form.blood_pressure"
                                    type="text"
                                    placeholder="120/80"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Pulse
                                </label>

                                <input
                                    v-model="form.pulse"
                                    type="text"
                                    placeholder="72"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Temperature
                                </label>

                                <input
                                    v-model="form.temperature"
                                    type="text"
                                    placeholder="98.6"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    SPO2 (oxygen in %)
                                </label>

                                <input
                                    v-model="form.oxygen"
                                    type="text"
                                    placeholder="99"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Height (in cm)
                                </label>

                                <input
                                    v-model="form.height"
                                    type="text"
                                    placeholder="170"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Weight (in KG)
                                </label>

                                <input
                                    v-model="form.weight"
                                    type="text"
                                    placeholder="68"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- CONSULTATION -->
                <!-- ===================================================== -->
                <div v-if="activeTab === 'consultation'" class="grid gap-6 xl:grid-cols-3">

                    <div class="space-y-6 xl:col-span-2">
                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Examination Notes
                                </h2>
                            </div>

                            <div class="p-6">
                                <textarea
                                    v-model="form.examination_notes"
                                    rows="6"
                                    placeholder="Write physical examination findings, clinical impression, and observations..."
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-100"
                                />
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Advice / Treatment Plan
                                </h2>
                            </div>

                            <div class="p-6">
                                <textarea
                                    v-model="form.advice"
                                    rows="5"
                                    placeholder="Lifestyle advice, treatment plan, precautions, and next steps..."
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-100"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Follow-up
                                </h2>
                            </div>

                            <div class="p-6">
                                <label class="mb-2 block text-sm font-semibold text-gray-700">
                                    Follow-up Date
                                </label>

                                <input
                                    v-model="form.follow_up_date"
                                    type="date"
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm"
                                >
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Doctor Notes
                                </h2>
                            </div>

                            <div class="p-6">
                                <textarea
                                    v-model="form.notes"
                                    rows="6"
                                    placeholder="Private notes, reminders, case remarks..."
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-100"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- PRESCRIPTION -->
                <!-- ===================================================== -->
                <div v-if="activeTab === 'prescription'">
                    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">

                        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                            <h2 class="text-lg font-bold text-gray-900">
                                Prescription
                            </h2>

                            <button
                                type="button"
                                @click="addMedicine"
                                class="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                + Add Medicine
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            Medicine
                                        </th>

                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            Dosage
                                        </th>

                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            Frequency
                                        </th>

                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            Duration
                                        </th>

                                         <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            Quantity
                                        </th>

                                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                            Instruction
                                        </th>

                                        <th class="px-6 py-4"></th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr
                                        v-for="(medicine, index) in form.medicines"
                                        :key="index"
                                    >
                                        <td class="px-6 py-4">
                                            <input
                                                v-model="medicine.medicine_name"
                                                type="text"
                                                placeholder="Medicine name"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"
                                            >
                                        </td>

                                        <td class="px-6 py-4">
                                            <input
                                                v-model="medicine.dosage"
                                                type="text"
                                                placeholder="500mg"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"
                                            >
                                        </td>

                                        <td class="px-6 py-4">
                                            <input
                                                v-model="medicine.frequency"
                                                type="text"
                                                placeholder="BD"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"
                                            >
                                        </td>

                                        <td class="px-6 py-4">
                                            <input
                                                v-model="medicine.duration"
                                                type="text"
                                                placeholder="5 Days"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            <input
                                                v-model="medicine.quantity"
                                                type="text"
                                                placeholder="10 Tablets"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"
                                            >
                                        </td>

                                        <td class="px-6 py-4">
                                            <input
                                                v-model="medicine.instruction"
                                                type="text"
                                                placeholder="After food"
                                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"
                                            >
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <button
                                                v-if="form.medicines.length > 1"
                                                type="button"
                                                @click="removeMedicine(index)"
                                                class="rounded-xl bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- LAB & TESTS -->
                <!-- ===================================================== -->
                <div v-if="activeTab === 'labs'" class="grid gap-6 xl:grid-cols-3">

                    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm xl:col-span-2">
                        <div class="border-b border-gray-100 px-6 py-5">
                            <h2 class="text-lg font-bold text-gray-900">
                                Lab & Tests
                            </h2>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-2">
                            <label
                                v-for="test in availableTests"
                                :key="test"
                                class="flex items-center gap-3 rounded-2xl border border-gray-200 p-4 transition hover:bg-gray-50"
                            >
                                <input
                                    type="checkbox"
                                    :checked="form.tests.includes(test)"
                                    @change="toggleTest(test)"
                                    class="rounded border-gray-300 text-indigo-600"
                                >

                                <span class="text-sm font-medium text-gray-700">
                                    {{ test }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Selected Tests
                                </h2>
                            </div>

                            <div class="space-y-3 p-6">
                                <div
                                    v-for="test in form.tests"
                                    :key="test"
                                    class="rounded-2xl bg-blue-50 p-4 text-sm font-medium text-blue-700"
                                >
                                    {{ test }}
                                </div>

                                <div v-if="!form.tests.length" class="rounded-2xl bg-gray-50 p-4 text-sm font-medium text-gray-500">
                                    No tests selected.
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Lab Notes
                                </h2>
                            </div>

                            <div class="p-6">
                                <textarea
                                    v-model="form.lab_notes"
                                    rows="5"
                                    placeholder="Instructions for lab, urgency, special notes..."
                                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-100"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- FILES -->
                <!-- ===================================================== -->
                <div v-if="activeTab === 'files'" class="grid gap-6 xl:grid-cols-3">

                    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm xl:col-span-2">
                        <div class="border-b border-gray-100 px-6 py-5">
                            <h2 class="text-lg font-bold text-gray-900">
                                Files & Attachments
                            </h2>
                        </div>

                        <div class="p-6">
                            <label class="flex cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center transition hover:border-indigo-400 hover:bg-indigo-50">
                                <div class="text-base font-semibold text-gray-900">
                                    Upload reports, images, prescriptions
                                </div>

                                <div class="mt-1 text-sm text-gray-500">
                                    PDF, JPG, PNG, DOC and more
                                </div>

                                <input
                                    type="file"
                                    multiple
                                    class="hidden"
                                    @change="handleFileChange"
                                >
                            </label>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 px-6 py-5">
                                <h2 class="text-lg font-bold text-gray-900">
                                    Selected Files
                                </h2>
                            </div>

                            <div class="space-y-3 p-6">
                                <div
                                    v-for="(file, index) in form.files"
                                    :key="index"
                                    class="flex items-center justify-between rounded-2xl bg-gray-50 p-4"
                                >
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900">
                                            {{ file.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ Math.round(file.size / 1024) }} KB
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="removeUploadedFile(index)"
                                        class="ml-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100"
                                    >
                                        Remove
                                    </button>
                                </div>

                                <div v-if="!form.files.length" class="rounded-2xl bg-gray-50 p-4 text-sm font-medium text-gray-500">
                                    No files selected.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- HISTORY -->
                <!-- ===================================================== -->
                <div v-if="activeTab === 'history'" class="space-y-4">
                    <div
                        v-for="item in history"
                        :key="item.id ?? item.title ?? item.date"
                        class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="mt-1 h-3 w-3 rounded-full"
                                :class="item.status === 'completed' ? 'bg-emerald-600' : 'bg-indigo-600'"
                            ></div>

                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ item.title ?? item.date ?? 'Visit History' }}
                                </div>

                                <div class="mt-2 text-sm leading-7 text-gray-600">
                                    {{ item.description ?? item.notes ?? 'No details available.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="!history.length" class="rounded-3xl border border-gray-200 bg-white p-6 text-sm text-gray-500 shadow-sm">
                        No previous history available for this patient.
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>