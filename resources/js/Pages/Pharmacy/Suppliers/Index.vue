<template>
  <AuthenticatedLayout title="Suppliers">

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Suppliers</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage medicine suppliers and their details</p>
      </div>
      <Link :href="route('pharmacy.suppliers.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        Add Supplier
      </Link>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <SummaryCard label="Total Suppliers"   :value="summary.total"   icon="check" color="slate" />
      <SummaryCard label="Active"            :value="summary.active"  icon="check" color="teal" />
      <SummaryCard label="License Expiring (30d)" :value="summary.expiring" icon="warning" color="amber" />
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex flex-wrap items-end gap-3">
      <div class="relative flex-1 min-w-52">
        <label for="filter-supplier-search" class="text-xs font-medium text-slate-500 mb-1 block">Search</label>
        <MagnifyingGlassIcon class="absolute left-3 top-9 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
        <input id="filter-supplier-search" v-model="filters.search" @input="debounceSearch" type="text"
               placeholder="Search name, phone, license no…"
               class="form-input pl-9" />
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-supplier-status" class="text-xs font-medium text-slate-500">Status</label>
        <select id="filter-supplier-status" v-model="filters.status" @change="applyFilters" class="form-select w-40">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
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
              <Th field="name"     :sort="filters" @sort="sortBy">Supplier</Th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Contact</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Drug License</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Credit Days</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Total Purchases</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
              <th class="px-4 py-3 w-12"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="suppliers.data.length">
              <tr v-for="s in suppliers.data" :key="s.id"
                  class="hover:bg-slate-50/60 transition-colors">

                <td class="px-4 py-3">
                  <Link :href="route('pharmacy.suppliers.show', s.id)"
                        class="font-semibold text-slate-800 hover:text-teal-700 transition">
                    {{ s.name }}
                  </Link>
                  <div class="text-xs text-slate-400 mt-0.5">{{ s.city }}</div>
                </td>

                <td class="px-4 py-3">
                  <div class="text-slate-700">{{ s.contact_person ?? '—' }}</div>
                  <div class="text-xs text-slate-400">{{ s.phone ?? '' }}</div>
                </td>

                <td class="px-4 py-3">
                  <div class="font-mono text-xs text-slate-700">{{ s.drug_license_no ?? '—' }}</div>
                  <div v-if="s.drug_license_expiry" class="text-xs mt-0.5"
                       :class="s.is_license_expired ? 'text-red-600 font-semibold' : 'text-slate-400'">
                    Exp: {{ formatDate(s.drug_license_expiry) }}
                    <span v-if="s.is_license_expired"> ⚠ Expired</span>
                  </div>
                </td>

                <td class="px-4 py-3 text-right font-mono text-slate-700">{{ s.credit_days }}d</td>

                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-800">
                  {{ formatCurrency(s.total_purchases) }}
                </td>

                <td class="px-4 py-3">
                  <span :class="s.is_active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                    : 'bg-slate-100 text-slate-500'"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
                    {{ s.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>

                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.suppliers.show', s.id)" icon="eye">View</ActionItem>
                    <ActionItem :href="route('pharmacy.suppliers.edit', s.id)" icon="edit">Edit</ActionItem>
                    <ActionItem :href="route('pharmacy.purchase-orders.create') + '?supplier_id=' + s.id" icon="document">
                      New PO
                    </ActionItem>
                    <ActionDivider />
                    <ActionItem @click="toggleActive(s)" icon="toggle">
                      {{ s.is_active ? 'Deactivate' : 'Activate' }}
                    </ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="7" class="py-16 text-center text-slate-400">
                <div class="flex flex-col items-center gap-3">
                  <TruckIcon class="w-12 h-12 opacity-30" />
                  <p class="text-sm font-medium">No suppliers found</p>
                  <Link :href="route('pharmacy.suppliers.create')" class="text-xs text-teal-600 underline">
                    Add your first supplier
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">Showing {{ suppliers.from }}–{{ suppliers.to }} of {{ suppliers.total }}</p>
        <Pagination :links="suppliers.links" />
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { PlusIcon, MagnifyingGlassIcon, TruckIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SummaryCard  from '@/Components/SummaryCard.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import ActionDivider from '@/Components/ActionDivider.vue'
import Th           from '@/Components/SortableTh.vue'

const props = defineProps({ suppliers: Object, filters: Object, summary: Object })

const filters = ref(
  Object.fromEntries(
    Object.entries(props.filters).map(([k, v]) => [k, v ?? ''])
  )
)
const hasActiveFilters = computed(() => Object.values(filters.value).some(v => v && v !== ''))

function applyFilters() {
  router.get(route('pharmacy.suppliers.index'), filters.value, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function clearFilters() { filters.value = {}; applyFilters() }
function sortBy(field) {
  filters.value.sort      = field
  filters.value.direction = filters.value.direction === 'asc' ? 'desc' : 'asc'
  applyFilters()
}
function toggleActive(s) {
  router.patch(route('pharmacy.suppliers.toggle-active', s.id), {}, { preserveScroll: true })
}
function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>