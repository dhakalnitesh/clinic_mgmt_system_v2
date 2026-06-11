<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({
    prescription: Object,
})

const isPrinting = ref(true)

onMounted(() => {
    setTimeout(() => window.print(), 500)
    setTimeout(() => {
        isPrinting.value = false
    }, 1000)
})

const goBack = () => window.history.back()
</script>

<template>
    <Head title="Print Prescription" />

    <div class="min-h-screen bg-white">
        <!-- Toolbar -->
        <div v-if="isPrinting" class="fixed top-0 inset-x-0 z-50 bg-indigo-600 text-white px-6 py-3 flex items-center justify-between print:hidden shadow-lg">
            <span class="font-semibold">Print Preview</span>
            <div class="flex items-center gap-3">
                <button @click="window.print()"
                        class="px-5 py-1.5 bg-white text-indigo-700 rounded-lg text-sm font-semibold hover:bg-indigo-50 transition">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
                <button @click="goBack"
                        class="px-4 py-1.5 text-sm font-medium text-white/80 hover:text-white transition">
                    &larr; Back
                </button>
            </div>
        </div>

        <!-- Prescription Content -->
        <div class="max-w-2xl mx-auto p-8 print:p-0">
            <!-- Header -->
            <div class="border-b-2 border-gray-800 pb-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Medical Prescription</h1>
                <p class="text-sm text-gray-500 mt-1">
                    #{{ prescription.prescription_number }}
                </p>
            </div>

            <!-- Patient & Doctor Info -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase text-gray-400 mb-1">Patient</h3>
                    <p class="font-medium text-gray-900">{{ prescription.patient_name }}</p>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase text-gray-400 mb-1">Doctor</h3>
                    <p class="font-medium text-gray-900">Dr. {{ prescription.doctor_name }}</p>
                </div>
            </div>

            <div class="mb-6 text-sm text-gray-500">
                <span>Date: {{ prescription.prescribed_at }}</span>
                <span v-if="prescription.created_at_bs" class="ml-4">BS: {{ prescription.created_at_bs }}</span>
            </div>

            <!-- Medicines Table -->
            <table class="w-full text-sm border-collapse mb-6">
                <thead>
                    <tr class="border-b-2 border-gray-300">
                        <th class="py-2 text-left font-semibold text-gray-700">#</th>
                        <th class="py-2 text-left font-semibold text-gray-700">Medicine</th>
                        <th class="py-2 text-left font-semibold text-gray-700">Dosage</th>
                        <th class="py-2 text-left font-semibold text-gray-700">Frequency</th>
                        <th class="py-2 text-right font-semibold text-gray-700">Duration</th>
                        <th class="py-2 text-right font-semibold text-gray-700">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, idx) in prescription.items" :key="item.id" class="border-b border-gray-200">
                        <td class="py-3 text-gray-500">{{ idx + 1 }}</td>
                        <td class="py-3 font-medium text-gray-900">{{ item.medicine_name }}</td>
                        <td class="py-3 text-gray-700">{{ item.dosage_instruction || '-' }}</td>
                        <td class="py-3 text-gray-700">{{ item.frequency?.replace(/_/g, ' ') || '-' }}</td>
                        <td class="py-3 text-right text-gray-700">{{ item.duration_days ? item.duration_days + 'd' : '-' }}</td>
                        <td class="py-3 text-right font-medium text-gray-900">{{ item.quantity_prescribed }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Notes -->
            <div v-if="prescription.notes" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-xs font-semibold uppercase text-gray-400 mb-1">Notes</h4>
                <p class="text-sm text-gray-700">{{ prescription.notes }}</p>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 pt-4 text-center text-xs text-gray-400">
                <p>This is a computer-generated prescription.</p>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body { margin: 0; padding: 0; }
    @page { margin: 15mm; }
}
</style>
