<template>
  <AuthenticatedLayout title="Goods Received Notes">

    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Goods Received Notes</h1>
        <p class="mt-0.5 text-sm text-slate-500">All stock receipts from suppliers</p>
      </div>
      <Link :href="route('pharmacy.grn.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        New GRN
      </Link>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <SummaryCard label="Total GRNs"   :value="summary.total"    color="slate" icon="document" />
      <SummaryCard label="Pending"      :value="summary.pending"  color="amber" icon="clock"
                   :clickable="true" @click="setStatus('pending')" />
      <SummaryCard label="Verified"     :value="summary.verified" color="teal"  icon="check"
                   :clickable="true" @click="setStatus('verified')" />
      <SummaryCard label="Posted"       :value="summary.posted"   color="slate" icon="check" />
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-1 mb-4">
      <button v-for="tab in tabs" :key="tab.value"
              @click="setStatus(tab.value)"
              :class="['px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                filters.status === tab.value ? 'bg-teal-600 text-white' : 'text-slate-600 hover:bg-slate-100']">
        {{ tab.label }}
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex flex-wrap items-end gap-3">
      <div class="relative flex-1 min-w-52">
        <label for="filter-grn-search" class="text-xs font-medium text-slate-500 mb-1 block">Search</label>
        <MagnifyingGlassIcon class="absolute left-3 top-9 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"/>
        <input id="filter-grn-search" v-model="filters.search" @input="debounceSearch" type="text"
               placeholder="GRN number, invoice, supplier…" class="form-input pl-9" />
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-grn-supplier" class="text-xs font-medium text-slate-500">Supplier</label>
        <select id="filter-grn-supplier" v-model="filters.supplier" @change="applyFilters" class="form-select w-48">
          <option value="">All Suppliers</option>
          <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <button v-if="hasActiveFilters" @click="clearFilters" class="text-sm text-slate-500 hover:text-slate-700 underline">Clear</button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">GRN No.</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Supplier</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">PO Ref</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Invoice</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Amount</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Received By</th>
              <th class="px-4 py-3 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="grns.data.length">
              <tr v-for="grn in grns.data" :key="grn.id" class="hover:bg-slate-50/60 transition-colors">
                <td class="px-4 py-3">
                  <Link :href="route('pharmacy.grn.show', grn.id)"
                        class="font-mono font-semibold text-teal-700 hover:text-teal-900 transition">
                    {{ grn.grn_number }}
                  </Link>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ formatDate(grn.received_date) }}</td>
                <td class="px-4 py-3 font-medium text-slate-700">{{ grn.supplier }}</td>
                <td class="px-4 py-3">
                  <span v-if="grn.po_number" class="font-mono text-xs text-blue-600">{{ grn.po_number }}</span>
                  <span v-else class="text-slate-300">—</span>
                </td>
                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ grn.invoice_number ?? '—' }}</td>
                <td class="px-4 py-3"><StatusBadge :status="grn.status" type="grn" /></td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(grn.total_amount) }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ grn.received_by }}</td>
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.grn.show', grn.id)" icon="eye">View</ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="9" class="py-16 text-center text-slate-400">
                <div class="flex flex-col items-center gap-3">
                  <InboxArrowDownIcon class="w-12 h-12 opacity-30"/>
                  <p class="text-sm font-medium">No GRNs found</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">Showing {{ grns.from ?? 0 }}–{{ grns.to ?? 0 }} of {{ grns.total }}</p>
        <Pagination :links="grns.links" />
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { PlusIcon, MagnifyingGlassIcon, InboxArrowDownIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout    from '@/Layouts/AuthenticatedLayout.vue'
import SummaryCard  from '@/Components/SummaryCard.vue'
import StatusBadge  from '@/Components/Pharmacy/StatusBadge.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'

const props = defineProps({ grns: Object, suppliers: Array, filters: Object, summary: Object })
const filters = ref(
  Object.fromEntries(
    Object.entries(props.filters).map(([k, v]) => [k, v ?? ''])
  )
)
const hasActiveFilters = computed(() => Object.values(filters.value).some(v => v && v !== ''))
const tabs = [
  { value: '',         label: 'All' },
  { value: 'pending',  label: 'Pending' },
  { value: 'verified', label: 'Verified' },
  { value: 'posted',   label: 'Posted' },
]
function applyFilters() {
  router.get(route('pharmacy.grn.index'), filters.value, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function clearFilters() { filters.value = {}; applyFilters() }
function setStatus(v) { filters.value.status = v; applyFilters() }
function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>