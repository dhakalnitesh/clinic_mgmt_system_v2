<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'

const page = usePage()
const todayAD = computed(() => new Date().toLocaleDateString('en-CA'))
const todayBS = computed(() => page.props.today_bs || '—')

const props = defineProps({
    labOrder: Object,
})

const isPrinting = ref(true)

const isAbnormal = (value, range) => {
    if (!value || !range) return false
    const num = parseFloat(value)
    if (isNaN(num)) return false
    const parts = range.split('-').map(s => parseFloat(s.trim()))
    if (parts.length !== 2 || parts.some(isNaN)) return false
    return num < parts[0] || num > parts[1]
}

onMounted(() => {
    setTimeout(() => window.print(), 300)
    setTimeout(() => {
        isPrinting.value = false
    }, 1000)
})

const goBack = () => window.history.back()
</script>

<template>
    <Head :title="'Lab Report - ' + labOrder.order_number" />

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
                <i class="fas fa-arrow-left mr-1"></i> Back
            </button>
        </div>
    </div>

    <div class="mx-auto max-w-4xl bg-white p-8 pt-16 print:pt-8">
        <div class="mb-8 border-b-2 border-gray-300 pb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laboratory Report</h1>
                    <p class="text-sm text-gray-500">Clinical Laboratory Test Results</p>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-gray-900">{{ labOrder.order_number }}</div>
                    <div class="text-sm text-gray-500">{{ new Date(labOrder.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</div>
                </div>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-2 gap-6">
            <div>
                <h3 class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Patient Information</h3>
                <div class="rounded-lg bg-gray-50 p-4">
                    <div class="text-lg font-bold text-gray-900">{{ labOrder.patient?.name }}</div>
                    <div class="text-sm text-gray-600">UHID: {{ labOrder.patient?.uhid }}</div>
                    <div class="text-sm text-gray-600" v-if="labOrder.patient?.gender">Gender: {{ labOrder.patient?.gender }}</div>
                    <div class="text-sm text-gray-600" v-if="labOrder.patient?.age">Age: {{ labOrder.patient?.age }}</div>
                </div>
            </div>
            <div>
                <h3 class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Order Information</h3>
                <div class="rounded-lg bg-gray-50 p-4">
                    <div class="text-sm text-gray-600">Doctor: <span class="font-semibold text-gray-900">{{ labOrder.doctor?.name }}</span></div>
                    <div class="text-sm text-gray-600">Order Date: {{ new Date(labOrder.created_at).toLocaleDateString() }} <span class="text-gray-400">(BS: {{ labOrder.created_at_bs || '—' }})</span></div>
                    <div class="text-sm text-gray-600" v-if="labOrder.notes">Notes: {{ labOrder.notes }}</div>
                </div>
            </div>
        </div>

        <div v-for="item in labOrder.items" :key="item.id" class="mb-6">
            <div class="mb-2 rounded-lg bg-indigo-50 px-4 py-3">
                <h3 class="text-base font-bold text-indigo-800">{{ item.lab_test?.name }}</h3>
                <p class="text-xs text-indigo-600" v-if="item.lab_test?.code">Code: {{ item.lab_test?.code }}</p>
            </div>

            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-300 bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600">Parameter</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600">Result</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600">Unit</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600">Reference Range</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-600">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="param in item.lab_test?.parameters ?? []"
                        :key="param.id"
                        class="border-b border-gray-200"
                        :class="{ 'bg-red-50': isAbnormal(
                            item.results?.find(r => r.lab_test_parameter_id === param.id)?.result_value,
                            param.reference_range
                        ) }"
                    >
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ param.name }}</td>
                        <td class="px-4 py-3 text-sm font-bold" :class="isAbnormal(
                            item.results?.find(r => r.lab_test_parameter_id === param.id)?.result_value,
                            param.reference_range
                        ) ? 'text-red-600' : 'text-gray-900'">
                            {{ item.results?.find(r => r.lab_test_parameter_id === param.id)?.result_value ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ param.unit ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ param.reference_range ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ item.results?.find(r => r.lab_test_parameter_id === param.id)?.remarks ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="labOrder.items?.length" class="mt-8 border-t border-gray-300 pt-4 text-center text-xs text-gray-400">
            <p>{{ todayAD }} (BS: {{ todayBS }})</p>
            <p class="mt-1">This is a computer-generated report. Signature not required.</p>
        </div>

        <div class="mt-8 text-center print:hidden">
            <button
                @click="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
            >
                <i class="fas fa-print"></i> Print / Save PDF
            </button>
            <a
                :href="route('laboratory.orders.results.export.pdf', labOrder.id)"
                class="ml-3 inline-flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-sm font-semibold text-white hover:bg-red-700"
            >
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            <a
                :href="route('laboratory.orders.results.export.csv', labOrder.id)"
                class="ml-3 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                <i class="fas fa-file-csv"></i> Download CSV
            </a>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    @page {
        margin: 10mm;
    }
}
</style>
