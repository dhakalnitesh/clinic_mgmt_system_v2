<template>
  <AuthenticatedLayout title="Supplier Returns">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Supplier Returns</h1>
        <p class="mt-0.5 text-sm text-slate-500">Track medicines returned to suppliers</p>
      </div>
      <Link :href="route('pharmacy.supplier-returns.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        New Return
      </Link>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
      <SummaryCard label="Total Returns"  :value="summary.total"     color="slate" icon="document" />
      <SummaryCard label="Draft"          :value="summary.draft"     color="amber" icon="warning"
                   :clickable="true" @click="setStatus('draft')" />
      <SummaryCard label="Completed"      :value="summary.completed" color="teal"  icon="check" />
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-52">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"/>
        <input v-model="filters.search" @input="debounceSearch" type="text"
               placeholder="Return number or supplier…" class="form-input pl-9" />
      </div>
      <select v-model="filters.status" @change="applyFilters" class="form-select w-36">
        <option value="">All Status</option>
        <option value="draft">Draft</option>
        <option value="sent">Sent</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Return No.</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Supplier</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Reason</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Amount</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">By</th>
              <th class="px-4 py-3 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="returns.data.length">
              <tr v-for="r in returns.data" :key="r.id" class="hover:bg-slate-50/60 transition-colors">
                <td class="px-4 py-3">
                  <Link :href="route('pharmacy.supplier-returns.show', r.id)"
                        class="font-mono font-semibold text-teal-700 hover:text-teal-900">
                    {{ r.return_number }}
                  </Link>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ formatDate(r.return_date) }}</td>
                <td class="px-4 py-3 font-medium text-slate-700">{{ r.supplier }}</td>
                <td class="px-4 py-3 text-xs text-slate-500 capitalize">{{ r.reason?.replace(/_/g, ' ') }}</td>
                <td class="px-4 py-3"><StatusBadge :status="r.status" type="supplier_return" /></td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(r.total_amount) }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ r.returned_by }}</td>
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.supplier-returns.show', r.id)" icon="eye">View</ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="8" class="py-16 text-center text-slate-400">
                <TruckIcon class="w-12 h-12 mx-auto mb-3 opacity-30" />
                <p class="text-sm">No supplier returns found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">Showing {{ returns.from ?? 0 }}–{{ returns.to ?? 0 }} of {{ returns.total }}</p>
        <Pagination :links="returns.links" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { PlusIcon, MagnifyingGlassIcon, TruckIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout   from '@/Layouts/AuthenticatedLayout.vue'
import SummaryCard from '@/Components/SummaryCard.vue'
import StatusBadge from '@/Components/Pharmacy/StatusBadge.vue'
import Pagination  from '@/Components/Pagination.vue'
import ActionMenu  from '@/Components/ActionMenu.vue'
import ActionItem  from '@/Components/ActionItem.vue'

const props = defineProps({ returns: Object, filters: Object, summary: Object })
const filters = ref({ ...props.filters })
function applyFilters() {
  router.get(route('pharmacy.supplier-returns.index'), filters.value, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function setStatus(v) { filters.value.status = v; applyFilters() }
function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>