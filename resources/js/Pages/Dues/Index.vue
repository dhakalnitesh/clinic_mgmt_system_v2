<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    dues: Object,
    patients: Array,
    filters: Object,
    stats: Object,
})

const showCreateModal = ref(false)

const form = useForm({
    patient_id: '',
    items: [{ description: '', quantity: 1, unit_price: 0 }],
    discount: 0,
    notes: '',
})

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

const removeItem = (index) => {
    form.items.splice(index, 1)
}

const subtotal = () => form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
const total = () => Math.max(0, subtotal() - form.discount)

const submitInvoice = () => {
    form.post(route('billing.invoices.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            showCreateModal.value = false
        }
    })
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(amount || 0)
}

const payInvoice = (invoice) => {
    if (confirm('Mark this invoice as paid in full?')) {
        router.patch(route('billing.invoices.pay', invoice.id), {
            amount: invoice.total - (invoice.payments?.reduce((s, p) => s + p.amount, 0) || 0),
            payment_method: 'cash',
        })
    }
}
</script>

<template>
    <Head title="Due Management" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-hand-holding-usd text-emerald-600"></i>
                        Due Management
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Track and manage outstanding patient payments</p>
                </div>
                <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-plus"></i> New Invoice
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-red-100 dark:bg-red-900/30">
                            <i class="fas fa-clock text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats?.pending_invoices || 0 }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Pending Invoices</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                            <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats?.partial_invoices || 0 }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Partial Paid</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                            <i class="fas fa-money-bill-wave text-emerald-600 dark:text-emerald-400 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(stats?.total_due) }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Total Due</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg bg-red-100 dark:bg-red-900/30">
                            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats?.overdue_count || 0 }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Overdue (30+ days)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <FilterBar
                route-name="dues.index"
                :filters="filters"
                search-placeholder="Invoice number, patient name, phone..."
            />

            <!-- Dues Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Invoice</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Patient</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Total</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Paid</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Due</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Date</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(due, index) in dues?.data || []" :key="due.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ due.invoice_number }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700">
                                            {{ due.patient?.name?.charAt(0) || 'P' }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ due.patient?.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ due.patient?.phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(due.total) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ formatCurrency(due.payments?.reduce((s, p) => s + p.amount, 0)) }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-red-600">{{ formatCurrency(due.total - (due.payments?.reduce((s, p) => s + p.amount, 0) || 0)) }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="{
                                        'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300': due.status === 'pending',
                                        'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300': due.status === 'partial',
                                    }">
                                        {{ due.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ due.created_at ? new Date(due.created_at).toLocaleDateString() : '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="payInvoice(due)"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                                        :disabled="due.status === 'paid'">
                                        <i class="fas fa-check mr-1"></i> Pay
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!dues?.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-check-circle text-3xl text-emerald-400 mb-3 block"></i>
                                    No outstanding dues. All invoices are paid!
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="dues?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-medium">{{ dues.from || 0 }}</span>
                        to <span class="font-medium">{{ dues.to || 0 }}</span>
                        of <span class="font-medium">{{ dues.total }}</span> results
                    </div>
                    <Pagination :links="dues.links" />
                </div>
            </div>

            <!-- Create Invoice Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                        <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-file-invoice"></i>
                            New Invoice
                        </h2>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
                    </div>
                    <form @submit.prevent="submitInvoice">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Patient <span class="text-red-500">*</span></label>
                            <select v-model="form.patient_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
                                <option value="">Select Patient</option>
                                <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }} - {{ p.phone }}</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-sm font-medium text-gray-700">Items</label>
                                <button type="button" @click="addItem" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Item</button>
                            </div>
                            <div v-for="(item, i) in form.items" :key="i" class="flex gap-2 mb-2">
                                <input v-model="item.description" placeholder="Description" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm" />
                                <input v-model.number="item.quantity" type="number" min="1" class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm" />
                                <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="Price" class="w-28 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm" />
                                <button v-if="form.items.length > 1" type="button" @click="removeItem(i)" class="text-red-500 px-2">&times;</button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount</label>
                            <input v-model.number="form.discount" type="number" min="0" step="0.01" class="w-40 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2.5 text-sm" />
                        </div>

                        <div class="mb-6 text-right text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                            Total: Rs. {{ total().toLocaleString() }}
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition text-sm">Create Invoice</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
