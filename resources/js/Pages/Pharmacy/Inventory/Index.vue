<template>
  <AuthenticatedLayout title="Inventory">

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory</h1>
        <p class="mt-0.5 text-sm text-slate-500">Batch-level stock tracking and expiry management</p>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
      <SummaryCard label="Total Medicines" :value="summary.total_medicines" icon="pills"    color="slate" />
      <SummaryCard label="Stock Value"     :value="summary.total_stock_value" icon="currency" color="teal" :currency="true" />
      <SummaryCard label="Low Stock"       :value="summary.low_stock_count"   icon="warning"  color="amber"
                   :clickable="true" @click="setExpiry('low')" />
      <SummaryCard label="Near Expiry (90d)" :value="summary.near_expiry_count" icon="clock" color="orange"
                   :clickable="true" @click="setExpiry('near')" />
      <SummaryCard label="Expired"         :value="summary.expired_count"    icon="x-circle" color="red" />
      <SummaryCard label="Out of Stock"    :value="summary.out_of_stock"     icon="warning"  color="red" />
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex flex-wrap items-end gap-3">
      <div class="relative flex-1 min-w-52">
        <label for="filter-inv-search" class="text-xs font-medium text-slate-500 mb-1 block">Search</label>
        <MagnifyingGlassIcon class="absolute left-3 top-9 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
        <input id="filter-inv-search" v-model="filters.search" @input="debounceSearch" type="text"
               placeholder="Search medicine name…"
               class="form-input pl-9" />
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-inv-expiry" class="text-xs font-medium text-slate-500">Expiry Status</label>
        <select id="filter-inv-expiry" v-model="filters.expiry" @change="applyFilters" class="form-select w-40">
          <option value="">All Expiry</option>
          <option value="near">Near Expiry (90d)</option>
          <option value="critical">Critical (30d)</option>
          <option value="expired">Expired</option>
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
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Supplier</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Quantity</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Purchase</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Sale</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Expiry</th>
              <th class="px-4 py-3 w-12"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="batches.data.length">
              <tr v-for="b in batches.data" :key="b.id"
                  class="hover:bg-slate-50/60 transition-colors">

                <td class="px-4 py-3">
                  <div class="font-semibold text-slate-800">{{ b.medicine_name }}</div>
                  <div class="text-xs text-slate-400 mt-0.5">
                    {{ b.strength }} · {{ b.form }} · {{ b.unit }}
                    <span v-if="b.category" class="ml-1 text-slate-300">({{ b.category }})</span>
                  </div>
                </td>

                <td class="px-4 py-3">
                  <Link :href="route('pharmacy.inventory.batch', b.id)"
                        class="font-mono text-xs text-teal-700 hover:text-teal-900 transition">
                    {{ b.batch_number }}
                  </Link>
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">{{ b.supplier ?? '—' }}</td>

                <td class="px-4 py-3 text-right font-mono font-semibold"
                    :class="b.quantity_available <= 0 ? 'text-red-600' : 'text-slate-900'">
                  {{ b.quantity_available }}
                  <span class="text-xs text-slate-400 font-normal">/ {{ b.quantity_received }}</span>
                </td>

                <td class="px-4 py-3 text-right font-mono text-slate-600">{{ formatCurrency(b.purchase_price) }}</td>
                <td class="px-4 py-3 text-right font-mono text-slate-600">{{ formatCurrency(b.sale_price) }}</td>

                <td class="px-4 py-3">
                  <ExpiryTag :date="b.expiry_date" :days="b.days_to_expiry" :status="b.expiry_status" />
                </td>

                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.inventory.batch', b.id)" icon="eye">Detail</ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="8" class="py-16 text-center text-slate-400">
                <div class="flex flex-col items-center gap-3">
                  <BeakerIcon class="w-12 h-12 opacity-30" />
                  <p class="text-sm font-medium">No inventory records found</p>
                  <div v-if="hasActiveFilters" class="text-xs">
                    Try adjusting your filters or
                    <button @click="clearFilters" class="text-teal-600 underline">clear all filters</button>
                  </div>
                  <Link v-else :href="route('pharmacy.grn.create')" class="text-xs text-teal-600 underline">
                    Receive stock via GRN
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">
          Showing {{ batches.from }}–{{ batches.to }} of {{ batches.total }} batches
        </p>
        <Pagination :links="batches.links" />
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { MagnifyingGlassIcon, BeakerIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SummaryCard  from '@/Components/SummaryCard.vue'
import ExpiryTag    from '@/Components/Pharmacy/ExpiryTag.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'

const props = defineProps({
  batches:  Object,
  filters:  Object,
  summary:  Object,
})

const filters = ref(
  Object.fromEntries(
    Object.entries(props.filters).map(([k, v]) => [k, v ?? ''])
  )
)

const hasActiveFilters = computed(() =>
  Object.values(filters.value).some(v => v && v !== '')
)

function applyFilters() {
  router.get(route('pharmacy.inventory.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

const debounceSearch = useDebounceFn(applyFilters, 350)

function clearFilters() {
  filters.value = {}
  applyFilters()
}

function setExpiry(val) {
  filters.value.expiry = val
  applyFilters()
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
</script>
