<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    invoice: Object,
    patientHistory: Array,
})

const paidAmount = (inv) => inv.payments?.reduce((s, p) => p.amount > 0 ? s + p.amount : s, 0) || 0

const statusClass = (status) => {
    switch (status) {
        case 'paid': return 'bg-emerald-100 text-emerald-700'
        case 'partial': return 'bg-amber-100 text-amber-700'
        case 'cancelled': return 'bg-gray-100 text-gray-500 line-through'
        default: return 'bg-red-100 text-red-700'
    }
}

const formatCurrency = (amount) => 'Rs. ' + Number(amount || 0).toLocaleString()
</script>

<template>
    <Head :title="'Invoice - ' + invoice.invoice_number" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6 max-w-5xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-file-invoice text-indigo-600"></i>
                        Invoice {{ invoice.invoice_number }}
                    </h1>
                    <p class="text-sm text-gray-500">Created: {{ new Date(invoice.created_at).toLocaleDateString() }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('billing.invoices')"
                          class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium transition">
                        <i class="fas fa-arrow-left"></i> Back
                    </Link>
                    <a :href="route('billing.invoices.print', invoice.id)" target="_blank"
                       class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-medium transition">
                        <i class="fas fa-print"></i> Print
                    </a>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-xl border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold text-gray-900">Patient Details</h2>
                        </div>
                        <div class="p-6 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Name</span>
                                <p class="font-medium text-gray-900">{{ invoice.patient?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">UHID</span>
                                <p class="font-medium text-gray-900">{{ invoice.patient?.uhid || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Phone</span>
                                <p class="font-medium text-gray-900">{{ invoice.patient?.phone || '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Gender</span>
                                <p class="font-medium text-gray-900">{{ invoice.patient?.gender || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold text-gray-900">Invoice Items</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Qty</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Rate</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(item, i) in invoice.items" :key="item.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-gray-700">{{ i + 1 }}</td>
                                        <td class="px-6 py-4 text-gray-900 font-medium">{{ item.description }}</td>
                                        <td class="px-6 py-4 text-right text-gray-700">{{ item.quantity }}</td>
                                        <td class="px-6 py-4 text-right text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                        <td class="px-6 py-4 text-right text-gray-900 font-medium">{{ formatCurrency(item.total_price) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="patientHistory.length" class="rounded-xl border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold text-gray-900">Patient Invoice History</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Invoice</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Paid</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="h in patientHistory" :key="h.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-mono text-gray-700 text-xs">{{ h.invoice_number }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ new Date(h.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-3 text-right text-gray-700">{{ formatCurrency(h.total) }}</td>
                                        <td class="px-6 py-3 text-right text-emerald-600">{{ formatCurrency(paidAmount(h)) }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(h.status)">{{ h.status }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <Link :href="route('billing.invoices.show', h.id)"
                                                  class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold text-gray-900">Summary</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Status</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(invoice.status)">{{ invoice.status }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-900">{{ formatCurrency(invoice.subtotal) }}</span>
                            </div>
                            <div v-if="invoice.discount > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount</span>
                                <span class="text-red-600">-{{ formatCurrency(invoice.discount) }}</span>
                            </div>
                            <div v-if="invoice.tax_percent > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Tax ({{ invoice.tax_percent }}%)</span>
                                <span class="text-gray-900">{{ formatCurrency(invoice.tax_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-bold border-t pt-3">
                                <span>Total</span>
                                <span class="text-indigo-600">{{ formatCurrency(invoice.total) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Paid</span>
                                <span class="text-emerald-600 font-medium">{{ formatCurrency(paidAmount(invoice)) }}</span>
                            </div>
                            <div v-if="invoice.total - paidAmount(invoice) > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Due</span>
                                <span class="text-red-600 font-medium">{{ formatCurrency(invoice.total - paidAmount(invoice)) }}</span>
                            </div>
                            <div v-if="invoice.due_date" class="flex justify-between text-sm">
                                <span class="text-gray-500">Due Date</span>
                                <span class="text-gray-900">{{ invoice.due_date }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="invoice.payments?.length" class="rounded-xl border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold text-gray-900">Payments</h2>
                        </div>
                        <div class="divide-y">
                            <div v-for="p in invoice.payments" :key="p.id" class="p-4 flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 capitalize">{{ p.payment_method }}</p>
                                    <p class="text-xs text-gray-500">{{ new Date(p.payment_date || p.created_at).toLocaleDateString() }}</p>
                                </div>
                                <span class="text-sm font-semibold" :class="p.amount < 0 ? 'text-red-600' : 'text-emerald-600'">
                                    {{ p.amount < 0 ? '-' : '' }}{{ formatCurrency(Math.abs(p.amount)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="invoice.notes" class="rounded-xl border bg-white shadow-sm">
                        <div class="border-b px-6 py-4">
                            <h2 class="font-semibold text-gray-900">Notes</h2>
                        </div>
                        <div class="p-4">
                            <p class="text-sm text-gray-700">{{ invoice.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
