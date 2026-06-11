<script setup>
import { onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    invoice: Object,
})

const paidAmount = (invoice) => invoice.payments?.reduce((s, p) => s + p.amount, 0) || 0

onMounted(() => {
    setTimeout(() => window.print(), 300)
})
</script>

<template>
    <Head title="Invoice - {{ invoice.invoice_number }}" />
    <div class="min-h-screen bg-gray-100 p-4 print:bg-white print:p-0">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8 print:shadow-none print:rounded-none">
            <div class="flex justify-between items-start border-b border-gray-300 pb-6 mb-6">
                <div>
                    <h1 class="text-2xl text-gray-900">Clinic Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Invoice #{{ invoice.invoice_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Date: {{ new Date(invoice.created_at).toLocaleDateString() }}</p>
                    <p class="text-sm text-gray-600">Status:
                        <span class="font-semibold uppercase" :class="{
                            'text-emerald-600': invoice.status === 'paid',
                            'text-amber-600': invoice.status === 'partial',
                            'text-red-600': invoice.status === 'pending',
                        }">{{ invoice.status }}</span>
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-600 uppercase mb-2">Patient Details</h2>
                <p class="text-lg font-semibold text-gray-900">{{ invoice.patient?.name || 'N/A' }}</p>
                <p class="text-sm text-gray-600">Phone: {{ invoice.patient?.phone || '-' }}</p>
                <p class="text-sm text-gray-600">UHID: {{ invoice.patient?.uhid || '-' }}</p>
            </div>

            <table class="w-full mb-6">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="text-left py-2 text-xs uppercase text-gray-600">#</th>
                        <th class="text-left py-2 text-xs uppercase text-gray-600">Description</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-600">Qty</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-600">Rate</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-600">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, i) in invoice.items || []" :key="item.id" class="border-b border-gray-200">
                        <td class="py-3 text-sm">{{ i + 1 }}</td>
                        <td class="py-3 text-sm">{{ item.description }}</td>
                        <td class="py-3 text-sm text-right">{{ item.quantity }}</td>
                        <td class="py-3 text-sm text-right">Rs. {{ Number(item.unit_price).toLocaleString() }}</td>
                        <td class="py-3 text-sm text-right">Rs. {{ Number(item.total_price || item.quantity * item.unit_price).toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end border-t border-gray-300 pt-4">
                <div class="w-64 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span>Rs. {{ Number(invoice.subtotal).toLocaleString() }}</span>
                    </div>
                    <div v-if="invoice.discount > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount</span>
                        <span class="text-red-600">- Rs. {{ Number(invoice.discount).toLocaleString() }}</span>
                    </div>
                     <div class="flex justify-between text-base border-t border-gray-300 pt-2">
                        <span>Total</span>
                        <span>Rs. {{ Number(invoice.total).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between text-sm pt-1">
                        <span class="text-gray-600">Paid</span>
                        <span class="text-emerald-600">Rs. {{ paidAmount(invoice).toLocaleString() }}</span>
                    </div>
                    <div v-if="invoice.total - paidAmount(invoice) > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600">Due</span>
                        <span class="text-red-600">Rs. {{ (invoice.total - paidAmount(invoice)).toLocaleString() }}</span>
                    </div>
                </div>
            </div>

            <div v-if="invoice.payments?.length" class="mt-6 border-t border-gray-300 pt-4">
                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-2">Payment History</h3>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-1 text-xs text-gray-500">Date</th>
                            <th class="text-left py-1 text-xs text-gray-500">Method</th>
                            <th class="text-right py-1 text-xs text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in invoice.payments" :key="p.id" class="border-b border-gray-100">
                            <td class="py-1 text-sm">{{ new Date(p.payment_date || p.created_at).toLocaleDateString() }}</td>
                            <td class="py-1 text-sm capitalize">{{ p.payment_method }}</td>
                            <td class="py-1 text-sm text-right">Rs. {{ Number(p.amount).toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="invoice.notes" class="mt-6 text-sm text-gray-600 border-t border-gray-300 pt-4">
                <p class="font-semibold">Notes:</p>
                <p>{{ invoice.notes }}</p>
            </div>

            <div class="mt-8 text-center text-xs text-gray-400 print:mt-4">
                <p>Generated by Clinic Management System | {{ new Date().toLocaleString() }}</p>
            </div>
        </div>
    </div>
</template>
