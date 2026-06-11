<template>
  <AuthenticatedLayout title="Sales">

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sales</h1>
        <p class="mt-0.5 text-sm text-slate-500">Dispensing history and invoices</p>
      </div>
      <Link :href="route('pharmacy.sales.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        New Sale
      </Link>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <SummaryCard label="Today's Sales"   :value="summary.today_count"  color="teal"  icon="check" />
      <SummaryCard label="Today's Revenue" :value="summary.today_amount" color="teal"  icon="currency" :currency="true" />
      <SummaryCard label="Total Sales"     :value="summary.total_count"  color="slate" icon="document" />
      <SummaryCard label="Returns"         :value="summary.returns"      color="amber" icon="warning" />
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex flex-wrap items-end gap-3">
      <div class="relative flex-1 min-w-52">
        <label for="filter-sales-search" class="text-xs font-medium text-slate-500 mb-1 block">Search</label>
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
        <input id="filter-sales-search" v-model="filters.search" @input="debounceSearch" type="text"
               placeholder="Search invoice number…" class="form-input pl-9" />
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-sales-status" class="text-xs font-medium text-slate-500">Status</label>
        <select id="filter-sales-status" v-model="filters.status" @change="applyFilters" class="form-select w-36">
          <option value="">All Status</option>
          <option value="completed">Completed</option>
          <option value="returned">Returned</option>
          <option value="partial_return">Partial Return</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-sales-type" class="text-xs font-medium text-slate-500">Type</label>
        <select id="filter-sales-type" v-model="filters.type" @change="applyFilters" class="form-select w-36">
          <option value="">All Types</option>
          <option value="counter">Counter</option>
          <option value="prescription">Prescription</option>
          <option value="opd">OPD</option>
          <option value="ipd">IPD</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-sales-from" class="text-xs font-medium text-slate-500">From</label>
        <input id="filter-sales-from" v-model="filters.date_from" @change="applyFilters" type="date" class="form-input w-36" />
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-sales-to" class="text-xs font-medium text-slate-500">To</label>
        <input id="filter-sales-to" v-model="filters.date_to" @change="applyFilters" type="date" class="form-input w-36" />
      </div>
      <button v-if="hasActiveFilters" @click="clearFilters" class="text-sm text-slate-500 hover:text-slate-700 underline">Clear</button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Invoice</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Payment</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Total</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Cashier</th>
              <th class="px-4 py-3 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="sales.data.length">
              <tr v-for="sale in sales.data" :key="sale.id"
                  class="hover:bg-slate-50/60 transition-colors">

                <td class="px-4 py-3">
                  <Link :href="route('pharmacy.sales.show', sale.id)"
                        class="font-mono font-semibold text-teal-700 hover:text-teal-900 transition">
                    {{ sale.invoice_number }}
                  </Link>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ formatDate(sale.sale_date) }}</td>
                <td class="px-4 py-3">
                  <span class="capitalize text-slate-600 text-xs font-medium">{{ sale.sale_type }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="capitalize text-slate-600 text-xs">{{ sale.payment_mode.replace('_', ' ') }}</span>
                </td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">
                  {{ formatCurrency(sale.total_amount) }}
                </td>
                <td class="px-4 py-3">
                  <span :class="statusClass(sale.status)"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
                    {{ statusLabel(sale.status) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ sale.cashier }}</td>
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.sales.show', sale.id)" icon="eye">View Invoice</ActionItem>
                    <ActionItem v-if="['completed','partial_return'].includes(sale.status)"
                                :href="route('pharmacy.sales.return.create', sale.id)" icon="trash" danger>
                      Return
                    </ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="8" class="py-16 text-center text-slate-400">
                <div class="flex flex-col items-center gap-3">
                  <ReceiptPercentIcon class="w-12 h-12 opacity-30" />
                  <p class="text-sm font-medium">No sales found</p>
                  <Link :href="route('pharmacy.sales.create')" class="text-xs text-teal-600 underline">
                    Create first sale
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">Showing {{ sales.from }}–{{ sales.to }} of {{ sales.total }}</p>
        <Pagination :links="sales.links" />
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router }  from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { PlusIcon, MagnifyingGlassIcon, ReceiptPercentIcon } from '@heroicons/vue/24/outline'
import SummaryCard  from '@/Components/SummaryCard.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'

const props = defineProps({ sales: Object, filters: Object, summary: Object })
const filters = ref(
  Object.fromEntries(
    Object.entries(props.filters).map(([k, v]) => [k, v ?? ''])
  )
)
const hasActiveFilters = computed(() => Object.values(filters.value).some(v => v && v !== ''))

function applyFilters() {
  router.get(route('pharmacy.sales.index'), filters.value, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function clearFilters() { filters.value = {}; applyFilters() }

function statusClass(s) {
  return {
    completed:      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    returned:       'bg-red-50 text-red-700 ring-1 ring-red-200',
    partial_return: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    draft:          'bg-slate-100 text-slate-600',
  }[s] ?? 'bg-slate-100 text-slate-500'
}
function statusLabel(s) {
  return { completed: 'Completed', returned: 'Returned', partial_return: 'Partial Return', draft: 'Draft' }[s] ?? s
}
function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>