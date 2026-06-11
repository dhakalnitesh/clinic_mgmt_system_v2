<template>
  <AuthenticatedLayout title="Purchase Orders">

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Purchase Orders</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage drug procurement and supplier orders</p>
      </div>
      <Link :href="route('pharmacy.purchase-orders.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        New Purchase Order
      </Link>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <SummaryCard label="Total Orders"  :value="summary.total"   color="slate" icon="document" />
      <SummaryCard label="Draft"         :value="summary.draft"   color="slate" icon="document"
                   :clickable="true" @click="setStatus('draft')" />
      <SummaryCard label="Sent"          :value="summary.sent"    color="teal"  icon="check"
                   :clickable="true" @click="setStatus('sent')" />
      <SummaryCard label="Partial"       :value="summary.partial" color="amber" icon="warning"
                   :clickable="true" @click="setStatus('partial')" />
      <SummaryCard label="Pending Value" :value="summary.pending_value" color="teal" icon="currency" :currency="true" />
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-1 mb-4">
      <button v-for="tab in statusTabs" :key="tab.value"
              @click="setStatus(tab.value)"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                filters.status === tab.value
                  ? 'bg-teal-600 text-white shadow-sm'
                  : 'text-slate-600 hover:bg-slate-100',
              ]">
        {{ tab.label }}
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex flex-wrap items-center gap-3">
      <div class="relative flex-1 min-w-52">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
        <input v-model="filters.search" @input="debounceSearch" type="text"
               placeholder="Search PO number or supplier…"
               class="form-input pl-9" />
      </div>
      <select v-model="filters.supplier" @change="applyFilters" class="form-select w-48">
        <option value="">All Suppliers</option>
        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
      <button v-if="hasActiveFilters" @click="clearFilters" class="text-sm text-slate-500 hover:text-slate-700 underline">Clear</button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">PO Number</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Supplier</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Order Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Expected</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Amount</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Ordered By</th>
              <th class="px-4 py-3 w-12"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="orders.data.length">
              <tr v-for="po in orders.data" :key="po.id"
                  class="hover:bg-slate-50/60 transition-colors">

                <td class="px-4 py-3">
                  <Link :href="route('pharmacy.purchase-orders.show', po.id)"
                        class="font-mono font-semibold text-teal-700 hover:text-teal-900 transition">
                    {{ po.po_number }}
                  </Link>
                </td>
                <td class="px-4 py-3 font-medium text-slate-700">{{ po.supplier }}</td>
                <td class="px-4 py-3 text-slate-600">{{ formatDate(po.order_date) }}</td>
                <td class="px-4 py-3 text-slate-500">{{ po.expected_delivery_date ? formatDate(po.expected_delivery_date) : '—' }}</td>
                <td class="px-4 py-3"><StatusBadge :status="po.status" type="po" /></td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(po.total_amount) }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ po.ordered_by }}</td>

                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.purchase-orders.show', po.id)" icon="eye">View</ActionItem>
                    <ActionItem v-if="po.status === 'draft'"
                                :href="route('pharmacy.purchase-orders.edit', po.id)" icon="edit">Edit</ActionItem>
                    <ActionItem v-if="po.status === 'draft'" @click="sendPo(po)" icon="check">Mark as Sent</ActionItem>
                    <ActionItem v-if="['sent','partial'].includes(po.status)"
                                :href="route('pharmacy.grn.create') + '?purchase_order_id=' + po.id" icon="document">
                      Receive Goods (GRN)
                    </ActionItem>
                    <ActionDivider v-if="['draft','sent'].includes(po.status)" />
                    <ActionItem v-if="['draft','sent'].includes(po.status)"
                                @click="cancelPo(po)" icon="trash" danger>Cancel</ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="8" class="py-16 text-center text-slate-400">
                <div class="flex flex-col items-center gap-3">
                  <DocumentTextIcon class="w-12 h-12 opacity-30" />
                  <p class="text-sm font-medium">No purchase orders found</p>
                  <Link :href="route('pharmacy.purchase-orders.create')"
                        class="text-xs text-teal-600 underline">Create your first order</Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">Showing {{ orders.from }}–{{ orders.to }} of {{ orders.total }}</p>
        <Pagination :links="orders.links" />
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router }  from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { PlusIcon, MagnifyingGlassIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'
import SummaryCard  from '@/Components/SummaryCard.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge  from '@/Components/Pharmacy/StatusBadge.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import ActionDivider from '@/Components/ActionDivider.vue'

const props    = defineProps({ orders: Object, suppliers: Array, filters: Object, summary: Object })
const filters  = ref({ ...props.filters })
const statusTabs = [
  { value: '',           label: 'All' },
  { value: 'draft',      label: 'Draft' },
  { value: 'sent',       label: 'Sent' },
  { value: 'partial',    label: 'Partial' },
  { value: 'received',   label: 'Received' },
  { value: 'cancelled',  label: 'Cancelled' },
]
const hasActiveFilters = computed(() => Object.values(filters.value).some(v => v && v !== ''))

function applyFilters() {
  router.get(route('pharmacy.purchase-orders.index'), filters.value, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function clearFilters()   { filters.value = {}; applyFilters() }
function setStatus(val)   { filters.value.status = val; applyFilters() }
function sendPo(po)       { router.patch(route('pharmacy.purchase-orders.send', po.id), {}, { preserveScroll: true }) }
function cancelPo(po)     { router.patch(route('pharmacy.purchase-orders.cancel', po.id), {}, { preserveScroll: true }) }
function formatCurrency(v){ return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0) }
function formatDate(d)    { return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
</script>