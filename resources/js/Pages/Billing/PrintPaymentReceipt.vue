<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
    payment: Object,
    invoice: Object,
})

const isPrinting = ref(true)
const todayAD = computed(() => new Date().toLocaleDateString('en-CA'))
const todayBS = computed(() => page.props.today_bs || '—')

const paidAmount = computed(() => props.invoice.payments?.reduce((s, p) => s + p.amount, 0) || 0)
const dueAmount = computed(() => Math.max(0, Number(props.invoice.total) - paidAmount.value))

onMounted(() => {
    setTimeout(() => window.print(), 300)
    setTimeout(() => { isPrinting.value = false }, 1000)
})

const goBack = () => window.history.back()
</script>

<template>
    <Head :title="'Receipt - ' + payment.receipt_number" />

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

    <div class="mx-auto max-w-2xl bg-white p-8 pt-16 print:pt-4">
        <div class="text-center mb-6 border-b-2 border-gray-800 pb-4">
            <h1 class="text-2xl font-bold text-gray-900">Payment Receipt</h1>
            <p class="font-mono text-lg font-semibold text-indigo-600 mt-1">{{ payment.receipt_number }}</p>
            <p class="text-sm text-gray-500">{{ todayAD }} (BS: {{ todayBS }})</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-1">Patient</h3>
                <p class="font-semibold text-gray-900">{{ invoice.patient?.name || 'N/A' }}</p>
                <p class="text-sm text-gray-600">Phone: {{ invoice.patient?.phone || '-' }}</p>
                <p class="text-sm text-gray-600">UHID: {{ invoice.patient?.uhid || '-' }}</p>
            </div>
            <div class="text-right">
                <h3 class="text-xs font-semibold uppercase text-gray-500 mb-1">Invoice</h3>
                <p class="font-mono font-semibold text-gray-900">{{ invoice.invoice_number }}</p>
                <p class="text-sm text-gray-600">{{ new Date(invoice.created_at).toLocaleDateString() }}</p>
            </div>
        </div>

        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="text-left py-2 text-xs font-semibold uppercase text-gray-600">Description</th>
                    <th class="text-right py-2 text-xs font-semibold uppercase text-gray-600">Qty</th>
                    <th class="text-right py-2 text-xs font-semibold uppercase text-gray-600">Price</th>
                    <th class="text-right py-2 text-xs font-semibold uppercase text-gray-600">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in invoice.items || []" :key="item.id" class="border-b border-gray-200">
                    <td class="py-2 text-gray-700">{{ item.description }}</td>
                    <td class="py-2 text-right font-mono text-gray-700">{{ item.quantity }}</td>
                    <td class="py-2 text-right font-mono text-gray-700">Rs. {{ Number(item.unit_price).toLocaleString() }}</td>
                    <td class="py-2 text-right font-mono text-gray-900">Rs. {{ Number(item.total_price).toLocaleString() }}</td>
                </tr>
            </tbody>
        </table>

        <div class="border-t-2 border-gray-300 pt-4">
            <div class="flex justify-end">
                <div class="w-64 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-mono">Rs. {{ Number(invoice.subtotal).toLocaleString() }}</span>
                    </div>
                    <div v-if="invoice.discount > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount</span>
                        <span class="font-mono text-red-600">- Rs. {{ Number(invoice.discount).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base border-t border-gray-300 pt-2">
                        <span>Total Amount</span>
                        <span class="font-mono">Rs. {{ Number(invoice.total).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between text-sm pt-1 border-t border-gray-200">
                        <span class="font-semibold text-gray-700">This Payment</span>
                        <span class="font-mono font-bold text-emerald-600">Rs. {{ Number(payment.amount).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Payment Method</span>
                        <span class="font-semibold capitalize">{{ payment.payment_method }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Paid</span>
                        <span class="font-mono text-emerald-600">Rs. {{ paidAmount.toLocaleString() }}</span>
                    </div>
                    <div v-if="dueAmount > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600">Remaining Due</span>
                        <span class="font-mono font-semibold text-red-600">Rs. {{ dueAmount.toLocaleString() }}</span>
                    </div>
                    <div v-else class="flex justify-between text-sm">
                        <span class="text-gray-600">Status</span>
                        <span class="font-semibold text-emerald-600">Fully Paid</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="payment.notes" class="mt-4 text-sm text-gray-600 border-t border-gray-200 pt-4">
            <span class="font-semibold">Notes:</span> {{ payment.notes }}
        </div>

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
