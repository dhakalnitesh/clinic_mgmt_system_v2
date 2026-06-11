<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
    invoices: Object,
    totals: Object,
    filters: Object,
})
</script>

<template>
    <Head title="Revenue Report" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('reports.index')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-xl"></i></Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Revenue Report</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Track clinic revenue and pending payments</p>
                    </div>
                </div>
                <a :href="route('reports.revenue.export')"
                    class="text-sm px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase">Total Collected</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">Rs. {{ (totals?.collected || 0).toLocaleString() }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase">Pending Amount</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">Rs. {{ (totals?.pending || 0).toLocaleString() }}</p>
                </div>
            </div>

            <FilterBar route-name="reports.revenue" :filters="filters" />

            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Invoice</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Patient</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(inv, i) in invoices?.data || []" :key="inv.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm">{{ ((invoices.current_page - 1) * invoices.per_page) + i + 1 }}</td>
                                <td class="px-6 py-4 font-mono text-sm font-semibold">{{ inv.invoice_number }}</td>
                                <td class="px-6 py-4">{{ inv.patient?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 font-mono">Rs. {{ Number(inv.total).toLocaleString() }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="{
                                        'bg-emerald-100 text-emerald-700': inv.status === 'paid',
                                        'bg-amber-100 text-amber-700': inv.status === 'partial',
                                        'bg-red-100 text-red-700': inv.status === 'pending',
                                    }">{{ inv.status }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ inv.created_at ? new Date(inv.created_at).toLocaleDateString() : '-' }}</td>
                            </tr>
                            <tr v-if="!invoices?.data?.length">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No invoices found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="invoices?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t px-6 py-4">
                    <Pagination :links="invoices.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>