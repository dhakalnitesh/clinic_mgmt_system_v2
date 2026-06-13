<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'

const page = usePage()
const todayAD = computed(() => new Date().toLocaleDateString('en-CA'))
const todayBS = computed(() => page.props.today_bs || '—')

const props = defineProps({
    prescription: Object,
})

onMounted(() => {
    setTimeout(() => window.print(), 500)
})


</script>

<template>
    <Head title="Print Prescription" />

    <div class="min-h-screen bg-white">
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
                <p>{{ todayAD }} (BS: {{ todayBS }})</p>
                <p class="mt-1">This is a computer-generated prescription.</p>
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
