<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    patient: Object,
    payments: Array,
})

const totalPaid = computed(() => props.payments.reduce((s, p) => s + Number(p.amount), 0))
</script>

<template>
    <Head :title="'Payment History - ' + patient.name" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('billing.payments')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-arrow-left text-xl"></i>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-history text-indigo-600"></i>
                        Payment History
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ patient.name }} · UHID: {{ patient.uhid || '-' }} · Phone: {{ patient.phone || '-' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total Payments</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ payments.length }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total Paid</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">Rs. {{ totalPaid.toLocaleString() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                    <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Payment Methods</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                        {{ [...new Set(payments.map(p => p.payment_method))].join(', ') || '—' }}
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Receipt No.</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Invoice</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Method</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Date</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(p, i) in payments" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ i + 1 }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-indigo-600 font-semibold">{{ p.receipt_number || '—' }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-gray-700 dark:text-gray-300">{{ p.invoice?.invoice_number || '-' }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-gray-700 dark:text-gray-300">Rs. {{ Number(p.amount).toLocaleString() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 capitalize">{{ p.payment_method }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ p.payment_date ? new Date(p.payment_date).toLocaleDateString() : '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a :href="route('billing.payments.receipt', p.id)" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800" title="Print Receipt">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!payments.length">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No payment history found for this patient.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
