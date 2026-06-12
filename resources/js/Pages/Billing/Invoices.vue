<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    invoices: Object,
    patients: Array,
    filters: Object,
})

const showCreateModal = ref(false)
const showPayModal = ref(null)

const form = useForm({
    patient_id: '',
    items: [{ description: '', quantity: 1, unit_price: 0 }],
    discount: 0,
    notes: '',
})

const payForm = useForm({
    amount: 0,
    payment_method: 'cash',
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

const submitPayment = (invoice) => {
    payForm.amount = payForm.amount || invoice.total - (invoice.payments?.reduce((s, p) => s + p.amount, 0) || 0)
    payForm.patch(route('billing.invoices.pay', invoice.id))
}

const statusClass = (status) => {
    switch (status) {
        case 'paid': return 'bg-emerald-100 text-emerald-700'
        case 'partial': return 'bg-amber-100 text-amber-700'
        default: return 'bg-red-100 text-red-700'
    }
}

const paidAmount = (invoice) => invoice.payments?.reduce((s, p) => s + p.amount, 0) || 0
</script>

<template>
    <Head title="Billing - Invoices" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-file-invoice text-indigo-600"></i>
                        Invoices
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Manage patient billing and invoices</p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="route('billing.invoices.print-all', filters)" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition"
                        title="Print All Invoices">
                        <i class="fas fa-print"></i> Print All
                    </a>
                    <button @click="showCreateModal = true"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow">
                        + New Invoice
                    </button>
                </div>
            </div>

            <FilterBar
                route-name="billing.invoices"
                :filters="filters"
                search-placeholder="Invoice number, patient name..."
            />

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">#</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Invoice No.</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Patient</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Paid</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Date</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(inv, index) in invoices?.data || []" :key="inv.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ ((invoices.current_page - 1) * invoices.per_page) + index + 1 }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-gray-700 dark:text-gray-300">{{ inv.invoice_number }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ inv.patient?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">Rs. {{ Number(inv.total).toLocaleString() }}</td>
                                <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">Rs. {{ paidAmount(inv).toLocaleString() }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusClass(inv.status)">{{ inv.status }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ inv.created_at ? new Date(inv.created_at).toLocaleDateString() : '-' }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a :href="route('billing.invoices.print', inv.id)" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800" title="Print Invoice">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <button v-if="inv.status !== 'paid'" @click="showPayModal = inv"
                                        class="text-emerald-600 hover:text-emerald-800" title="Record Payment">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!invoices?.data?.length">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No invoices found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="invoices?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-medium">{{ invoices.from || 0 }}</span>
                        to <span class="font-medium">{{ invoices.to || 0 }}</span>
                        of <span class="font-medium">{{ invoices.total }}</span> results
                    </div>
                    <Pagination :links="invoices.links" />
                </div>
            </div>

            <!-- Create Invoice Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
                <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                        <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-file-invoice"></i>
                            New Invoice
                        </h2>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
                    </div>
                    <form @submit.prevent="submitInvoice">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Patient <span class="text-red-500">*</span></label>
                            <select v-model="form.patient_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
                                <option value="">Select Patient</option>
                                <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }} - {{ p.phone }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-sm font-medium text-gray-700">Items</label>
                                <button type="button" @click="addItem" class="text-xs text-indigo-600 hover:text-indigo-800">+ Add Item</button>
                            </div>
                            <div v-for="(item, i) in form.items" :key="i" class="flex gap-1.5 mb-1.5">
                                <input v-model="item.description" placeholder="Description" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-2.5 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                <input v-model.number="item.quantity" type="number" min="1" class="w-16 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-2 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="Price" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-2 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                <button v-if="form.items.length > 1" type="button" @click="removeItem(i)" class="text-red-500 px-1.5">&times;</button>
                            </div>
                        </div>

                        <div class="mb-3 flex items-center gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount</label>
                                <input v-model.number="form.discount" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                            </div>
                            <div class="text-right">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total</label>
                                <div class="text-lg font-semibold text-indigo-600">Rs. {{ total().toLocaleString() }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition text-sm">Create Invoice</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment Modal -->
            <div v-if="showPayModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Record Payment</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Invoice: <span class="font-semibold">{{ showPayModal.invoice_number }}</span></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Patient: <span class="font-semibold">{{ showPayModal.patient?.name }}</span></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Total: Rs. {{ Number(showPayModal.total).toLocaleString() }} | Paid: Rs. {{ paidAmount(showPayModal).toLocaleString() }}</p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                        <input v-model.number="payForm.amount" type="number" min="0.01" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                        <select v-model="payForm.payment_method" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="online">Online</option>
                            <option value="insurance">Insurance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea v-model="payForm.notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="showPayModal = null" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Cancel</button>
                        <button @click="submitPayment(showPayModal)" :disabled="payForm.processing" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Record Payment</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>