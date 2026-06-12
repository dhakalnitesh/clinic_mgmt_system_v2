<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
    payments: Object,
    filters: Object,
})
</script>

<template>
    <Head title="Payments" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('billing.invoices')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-xl"></i></Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-hand-holding-usd text-indigo-600"></i>
                        Payments
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Payment history and records</p>
                </div>
            </div>

            <FilterBar route-name="billing.payments" :filters="filters" />

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Receipt No.</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Invoice</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Patient</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Method</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Date</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(p, i) in payments?.data || []" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ ((payments.current_page - 1) * payments.per_page) + i + 1 }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-indigo-600 font-semibold">{{ p.receipt_number || '—' }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-gray-700 dark:text-gray-300">{{ p.invoice?.invoice_number || '-' }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ p.invoice?.patient?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">Rs. {{ Number(p.amount).toLocaleString() }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300"><span class="capitalize">{{ p.payment_method }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ p.payment_date ? new Date(p.payment_date).toLocaleDateString() : '-' }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Link :href="route('billing.payments.patient', p.invoice?.patient_id)"
                                        class="text-indigo-600 hover:text-indigo-800" title="View Payment History">
                                        <i class="fas fa-eye"></i>
                                    </Link>
                                    <a :href="route('billing.payments.receipt', p.id)" target="_blank"
                                        class="text-emerald-600 hover:text-emerald-800" title="Print Receipt">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!payments?.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No payments found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="payments?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t px-6 py-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ payments.from || 0 }}</span>
                        to <span class="font-medium">{{ payments.to || 0 }}</span>
                        of <span class="font-medium">{{ payments.total }}</span> results
                    </div>
                    <Pagination :links="payments.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
