<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'
import Combobox from '@/Components/Combobox.vue'

const props = defineProps({
    invoices: Object,
    patients: Array,
    filters: Object,
    stats: Object,
})

const showCreateModal = ref(false)
const showPayModal = ref(null)
const showEditModal = ref(false)
const showRefundModal = ref(null)
const editingInvoice = ref(null)

const form = useForm({
    patient_id: '',
    items: [{ description: '', quantity: 1, unit_price: 0 }],
    discount: 0,
    tax_percent: 0,
    due_date: '',
    notes: '',
})

const editForm = useForm({
    patient_id: '',
    items: [{ description: '', quantity: 1, unit_price: 0 }],
    discount: 0,
    tax_percent: 0,
    due_date: '',
    notes: '',
})

const payForm = useForm({
    amount: 0,
    payment_method: 'cash',
    notes: '',
})

const refundForm = useForm({
    amount: 0,
    notes: '',
})

const addItem = (f) => {
    f.items.push({ description: '', quantity: 1, unit_price: 0 })
}

const removeItem = (f, index) => {
    f.items.splice(index, 1)
}

const subtotal = (items) => items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)

const taxAmount = (items, taxPercent) => {
    const st = subtotal(items)
    return st * (taxPercent || 0) / 100
}

const total = (items, discount, taxPercent) => {
    const st = subtotal(items)
    return Math.max(0, st - (discount || 0) + taxAmount(items, taxPercent))
}

const submitInvoice = () => {
    form.post(route('billing.invoices.store'), {
        preserveScroll: true,
        onError: () => {
            // stay open so validation errors are visible
        },
    })
}

const openEditModal = (inv) => {
    editingInvoice.value = inv
    editForm.patient_id = inv.patient_id
    editForm.items = inv.items.map(i => ({
        description: i.description,
        quantity: i.quantity,
        unit_price: i.unit_price,
    }))
    editForm.discount = inv.discount
    editForm.tax_percent = inv.tax_percent || 0
    editForm.due_date = inv.due_date || ''
    editForm.notes = inv.notes || ''
    showEditModal.value = true
}

const submitEdit = () => {
    editForm.patch(route('billing.invoices.update', editingInvoice.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false
            editingInvoice.value = null
        }
    })
}

const submitPayment = (invoice) => {
    payForm.amount = payForm.amount || invoice.total - paidAmount(invoice)
    payForm.patch(route('billing.invoices.pay', invoice.id))
}

const cancelInvoice = (invoice) => {
    if (confirm('Are you sure you want to cancel invoice ' + invoice.invoice_number + '?')) {
        router.patch(route('billing.invoices.cancel', invoice.id))
    }
}

const openRefundModal = (invoice) => {
    showRefundModal.value = invoice
    refundForm.amount = invoice.paid_amount
    refundForm.notes = ''
}

const submitRefund = () => {
    refundForm.post(route('billing.invoices.refund', showRefundModal.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRefundModal.value = null
            refundForm.reset()
        }
    })
}

const deleteInvoice = (invoice) => {
    if (confirm('Delete invoice ' + invoice.invoice_number + '? This cannot be undone.')) {
        router.delete(route('billing.invoices.destroy', invoice.id), {
            preserveScroll: true,
        })
    }
}

const statusClass = (status) => {
    switch (status) {
        case 'paid': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
        case 'partial': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
        case 'cancelled': return 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 line-through'
        default: return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
    }
}

const paidAmount = (invoice) => invoice.payments?.reduce((s, p) => p.amount > 0 ? s + p.amount : s, 0) || 0

const formatCurrency = (amount) => {
    return 'Rs. ' + Number(amount || 0).toLocaleString()
}
</script>

<template>
    <Head title="Billing - Invoices" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-3 text-center">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(stats?.today_revenue) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Today's Revenue</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-3 text-center">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(stats?.today_collected) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Collected Today</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-3 text-center">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ stats?.pending_count || 0 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Pending</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-3 text-center">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ stats?.overdue_count || 0 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Overdue</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-3 text-center">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(stats?.total_outstanding) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Outstanding</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-3 text-center">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ stats?.cancelled_count || 0 }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Cancelled</div>
                </div>
            </div>

            <FilterBar
                route-name="billing.invoices"
                :filters="filters"
                search-placeholder="Invoice number, patient name..."
            />

            <!-- Invoices Table -->
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
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Due</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="(inv, index) in invoices?.data || []" :key="inv.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ ((invoices.current_page - 1) * invoices.per_page) + index + 1 }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-gray-700 dark:text-gray-300">{{ inv.invoice_number }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ inv.patient?.name || 'N/A' }}</td>
                                <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">{{ formatCurrency(inv.total) }}</td>
                                <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">{{ formatCurrency(paidAmount(inv)) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusClass(inv.status)">{{ inv.status }}</span>
                                </td>
                                <td class="px-6 py-4 font-mono text-sm" :class="inv.total - paidAmount(inv) > 0 ? 'text-red-600 font-bold' : 'text-gray-400'">
                                    {{ formatCurrency(Math.max(0, inv.total - paidAmount(inv))) }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Link :href="route('billing.invoices.show', inv.id)"
                                        class="text-gray-600 hover:text-indigo-800" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </Link>
                                    <a :href="route('billing.invoices.print', inv.id)" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800" title="Print Invoice">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <button v-if="inv.status !== 'cancelled'" @click="openEditModal(inv)"
                                        class="text-blue-600 hover:text-blue-800" title="Edit Invoice">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button v-if="inv.status !== 'paid' && inv.status !== 'cancelled'" @click="showPayModal = inv"
                                        class="text-emerald-600 hover:text-emerald-800" title="Record Payment">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </button>
                                    <button v-if="paidAmount(inv) > 0 && inv.status !== 'cancelled'" @click="openRefundModal(inv)"
                                        class="text-orange-600 hover:text-orange-800" title="Refund">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button v-if="inv.status !== 'paid' && inv.status !== 'cancelled'" @click="cancelInvoice(inv)"
                                        class="text-red-600 hover:text-red-800" title="Cancel Invoice">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    <button v-if="inv.payments?.length === 0" @click="deleteInvoice(inv)"
                                        class="text-gray-400 hover:text-red-600" title="Delete Invoice">
                                        <i class="fas fa-trash"></i>
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
                <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-indigo-600 flex items-center gap-2">
                            <i class="fas fa-file-invoice"></i>
                            New Invoice
                        </h2>
                        <button @click="showCreateModal = false" class="text-gray-400 hover:text-red-500 text-2xl font-bold leading-none">&times;</button>
                    </div>

                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto space-y-4">
                        <div v-if="form.errors.patient_id || form.errors.items || form.errors.error"
                             class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                            <p v-if="form.errors.error" class="text-sm text-red-600">{{ form.errors.error }}</p>
                            <p v-if="form.errors.patient_id" class="text-sm text-red-600">{{ form.errors.patient_id }}</p>
                            <p v-if="form.errors.items" class="text-sm text-red-600">{{ form.errors.items }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                Patient <span class="text-red-500">*</span>
                            </label>
                            <Combobox
                                v-model="form.patient_id"
                                :options="patients"
                                label-key="name"
                                value-key="id"
                                placeholder="Search patient..."
                                class="mt-1"
                            />
                            <p v-if="form.errors.patient_id" class="text-xs text-red-500 mt-0.5">{{ form.errors.patient_id }}</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm font-medium text-gray-700">Items <span class="text-red-500">*</span></label>
                                <button type="button" @click="addItem(form)"
                                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                            <p v-if="form.errors.items" class="text-xs text-red-600 mb-2">{{ form.errors.items }}</p>

                            <div class="space-y-2">
                                <div v-for="(item, i) in form.items" :key="i"
                                     class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-b border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">{{ i + 1 }}</span>
                                            <span class="font-medium text-gray-800 text-sm truncate">{{ item.description || 'New item' }}</span>
                                        </div>
                                        <button v-if="form.items.length > 1" type="button" @click="removeItem(form, i)"
                                                class="text-gray-400 hover:text-red-500 text-lg leading-none">&times;</button>
                                    </div>
                                    <div class="p-3 grid grid-cols-3 gap-2">
                                        <div class="col-span-3">
                                            <label class="text-xs text-gray-500">Description</label>
                                            <input v-model="item.description" placeholder="Item description"
                                                   class="w-full mt-0.5 border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Qty</label>
                                            <input v-model.number="item.quantity" type="number" min="1"
                                                   class="w-full mt-0.5 border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Unit Price</label>
                                            <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="0.00"
                                                   class="w-full mt-0.5 border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                        </div>
                                        <div class="flex items-end justify-end pb-1">
                                            <span class="text-sm font-semibold text-gray-700">Rs. {{ (item.quantity * item.unit_price).toLocaleString() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!form.items.length"
                                 class="border-2 border-dashed border-gray-200 rounded-lg py-8 text-center text-gray-400 mt-2">
                                <i class="fas fa-receipt text-2xl mb-1 opacity-40"></i>
                                <p class="text-sm">Click "Add Item" above</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Discount (Rs.)</label>
                                <input v-model.number="form.discount" type="number" min="0" step="0.01"
                                       class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tax (%)</label>
                                <input v-model.number="form.tax_percent" type="number" min="0" max="100" step="0.01"
                                       class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">Due Date</label>
                            <input v-model="form.due_date" type="date"
                                   class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">Notes</label>
                            <textarea v-model="form.notes" rows="2"
                                      class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition resize-none"></textarea>
                        </div>

                        <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-1">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ formatCurrency(subtotal(form.items)) }}</span>
                            </div>
                            <div v-if="form.tax_percent > 0" class="flex justify-between text-sm text-gray-600">
                                <span>Tax ({{ form.tax_percent }}%)</span>
                                <span>{{ formatCurrency(taxAmount(form.items, form.tax_percent)) }}</span>
                            </div>
                            <div v-if="form.discount > 0" class="flex justify-between text-sm text-gray-600">
                                <span>Discount</span>
                                <span>-{{ formatCurrency(form.discount) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-bold text-indigo-600 border-t border-gray-200 pt-1">
                                <span>Total</span>
                                <span>{{ formatCurrency(total(form.items, form.discount, form.tax_percent)) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-medium transition">
                            Cancel
                        </button>
                        <button type="button" :disabled="form.processing" @click="submitInvoice"
                                class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 text-sm font-medium transition">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin mr-1"></i>
                            {{ form.processing ? 'Saving...' : 'Create Invoice' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Invoice Modal -->
            <div v-if="showEditModal && editingInvoice" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
                <div class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                        <h2 class="text-xl font-bold text-blue-600 dark:text-blue-400 flex items-center gap-2">
                            <i class="fas fa-edit"></i>
                            Edit Invoice
                        </h2>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Invoice: <span class="font-semibold">{{ editingInvoice.invoice_number }}</span></p>
                    <form @submit.prevent="submitEdit">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Patient <span class="text-red-500">*</span></label>
                            <Combobox
                                v-model="editForm.patient_id"
                                :options="patients"
                                label-key="name"
                                value-key="id"
                                placeholder="Search patient..."
                            />
                        </div>

                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-sm font-medium text-gray-700">Items</label>
                                <button type="button" @click="addItem(editForm)" class="text-xs text-indigo-600 hover:text-indigo-800">+ Add Item</button>
                            </div>
                            <div v-for="(item, i) in editForm.items" :key="i" class="flex gap-1.5 mb-1.5">
                                <input v-model="item.description" placeholder="Description" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-2.5 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                <input v-model.number="item.quantity" type="number" min="1" class="w-16 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-2 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="Price" class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-2 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                                <button v-if="editForm.items.length > 1" type="button" @click="removeItem(editForm, i)" class="text-red-500 px-1.5">&times;</button>
                            </div>
                        </div>

                        <div class="mb-3 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount (Rs.)</label>
                                <input v-model.number="editForm.discount" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax (%)</label>
                                <input v-model.number="editForm.tax_percent" type="number" min="0" max="100" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                            <input v-model="editForm.due_date" type="date" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea v-model="editForm.notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"></textarea>
                        </div>

                        <div class="mb-4 text-right">
                            <div class="text-xs text-gray-500">Subtotal: {{ formatCurrency(subtotal(editForm.items)) }}</div>
                            <div v-if="editForm.tax_percent > 0" class="text-xs text-gray-500">Tax ({{ editForm.tax_percent }}%): {{ formatCurrency(taxAmount(editForm.items, editForm.tax_percent)) }}</div>
                            <div v-if="editForm.discount > 0" class="text-xs text-gray-500">Discount: -{{ formatCurrency(editForm.discount) }}</div>
                            <div class="text-lg font-semibold text-blue-600">{{ formatCurrency(total(editForm.items, editForm.discount, editForm.tax_percent)) }}</div>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm">Cancel</button>
                            <button type="submit" :disabled="editForm.processing" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm">Update Invoice</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment Modal -->
            <div v-if="showPayModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Record Payment</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Invoice: <span class="font-semibold">{{ showPayModal.invoice_number }}</span></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Patient: <span class="font-semibold">{{ showPayModal.patient?.name }}</span></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Total: {{ formatCurrency(showPayModal.total) }} | Paid: {{ formatCurrency(paidAmount(showPayModal)) }}</p>

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

            <!-- Refund Modal -->
            <div v-if="showRefundModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                    <h2 class="text-xl font-bold text-orange-600 dark:text-orange-400 mb-4">Process Refund</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Invoice: <span class="font-semibold">{{ showRefundModal.invoice_number }}</span></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Patient: <span class="font-semibold">{{ showRefundModal.patient?.name }}</span></p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Total Paid: {{ formatCurrency(showRefundModal.paid_amount) }} | Max Refund: {{ formatCurrency(showRefundModal.paid_amount) }}</p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Refund Amount <span class="text-red-500">*</span></label>
                        <input v-model.number="refundForm.amount" type="number" min="0.01" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                        <textarea v-model="refundForm.notes" rows="2" placeholder="Reason for refund..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button @click="showRefundModal = null" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Cancel</button>
                        <button @click="submitRefund" :disabled="refundForm.processing" class="px-4 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-700">Process Refund</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
