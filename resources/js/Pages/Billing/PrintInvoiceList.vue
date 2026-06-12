<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
    invoices: Array,
    filters: Object,
})

const isPrinting = ref(true)
const todayAD = computed(() => new Date().toLocaleDateString('en-CA'))
const todayBS = computed(() => page.props.today_bs || '—')

const totalAmount = computed(() => props.invoices.reduce((s, inv) => s + Number(inv.total), 0))
const totalPaid = computed(() => props.invoices.reduce((s, inv) => s + Number(inv.paid_amount || 0), 0))

onMounted(() => {
    setTimeout(() => window.print(), 500)
    setTimeout(() => { isPrinting.value = false }, 1000)
})

const goBack = () => window.history.back()

const statusClass = (status) => {
    switch (status) {
        case 'paid': return 'text-emerald-600'
        case 'partial': return 'text-amber-600'
        default: return 'text-red-600'
    }
}
</script>

<template>
    <Head title="Invoices Report" />

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

    <div class="mx-auto max-w-6xl bg-white p-8 pt-16 print:pt-4">
        <div class="text-center mb-6 border-b border-gray-300 pb-4">
            <h1 class="text-2xl font-bold text-gray-900">Invoice Report</h1>
            <p class="text-sm text-gray-500">{{ todayAD }} (BS: {{ todayBS }})</p>
            <p v-if="filters?.status" class="text-xs text-gray-400 mt-1">Filtered by: {{ filters.status }}</p>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-2 text-xs font-semibold uppercase text-gray-600">#</th>
                    <th class="text-left py-2 text-xs font-semibold uppercase text-gray-600">Invoice</th>
                    <th class="text-left py-2 text-xs font-semibold uppercase text-gray-600">Patient</th>
                    <th class="text-right py-2 text-xs font-semibold uppercase text-gray-600">Total</th>
                    <th class="text-right py-2 text-xs font-semibold uppercase text-gray-600">Paid</th>
                    <th class="text-right py-2 text-xs font-semibold uppercase text-gray-600">Due</th>
                    <th class="text-right py-2 text-xs font-semibold uppercase text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(inv, i) in invoices" :key="inv.id" class="border-b border-gray-200">
                    <td class="py-2 text-gray-600">{{ i + 1 }}</td>
                    <td class="py-2 font-mono font-semibold text-gray-900">{{ inv.invoice_number }}</td>
                    <td class="py-2 text-gray-700">{{ inv.patient?.name || 'N/A' }}</td>
                    <td class="py-2 text-right font-mono">Rs. {{ Number(inv.total).toLocaleString() }}</td>
                    <td class="py-2 text-right font-mono">Rs. {{ Number(inv.paid_amount || 0).toLocaleString() }}</td>
                    <td class="py-2 text-right font-mono">Rs. {{ (Number(inv.total) - Number(inv.paid_amount || 0)).toLocaleString() }}</td>
                    <td class="py-2 text-right font-semibold" :class="statusClass(inv.status)">
                        <span class="capitalize">{{ inv.status }}</span>
                    </td>
                </tr>
            </tbody>
            <tfoot class="border-t-2 border-gray-800">
                <tr>
                    <td colspan="3" class="py-3 text-right font-bold text-gray-900">Total</td>
                    <td class="py-3 text-right font-mono font-bold">Rs. {{ totalAmount.toLocaleString() }}</td>
                    <td class="py-3 text-right font-mono font-bold">Rs. {{ totalPaid.toLocaleString() }}</td>
                    <td class="py-3 text-right font-mono font-bold">Rs. {{ (totalAmount - totalPaid).toLocaleString() }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-8 text-center text-xs text-gray-400">
            <p>{{ todayAD }} (BS: {{ todayBS }})</p>
        </div>
    </div>
</template>

<style>
@media print {
    body { margin: 0; padding: 0; }
    @page { margin: 10mm; }
}
</style>
