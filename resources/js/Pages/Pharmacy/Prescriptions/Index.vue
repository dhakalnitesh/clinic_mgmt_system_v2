<template>
  <AuthenticatedLayout title="Prescriptions">

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Prescriptions</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage and dispense patient prescriptions</p>
      </div>
      <Link :href="route('pharmacy.prescriptions.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        New Prescription
      </Link>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <SummaryCard label="Pending"   :value="summary.pending"   color="amber" icon="clock"
                   :clickable="true" @click="setStatus('pending')" />
      <SummaryCard label="Partial"   :value="summary.partial"   color="orange" icon="warning"
                   :clickable="true" @click="setStatus('partial')" />
      <SummaryCard label="Dispensed" :value="summary.dispensed" color="teal"  icon="check" />
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
               placeholder="Search prescription number…"
               class="form-input pl-9" />
      </div>
      <button v-if="hasActiveFilters" @click="clearFilters"
              class="text-sm text-slate-500 hover:text-slate-700 underline">Clear</button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Rx Number</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Patient</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Doctor</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Dispensed By</th>
              <th class="px-4 py-3 w-12"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="prescriptions.data.length">
              <tr v-for="rx in prescriptions.data" :key="rx.id"
                  class="hover:bg-slate-50/60 transition-colors">

                <td class="px-4 py-3">
                  <Link :href="route('pharmacy.prescriptions.show', rx.id)"
                        class="font-mono font-semibold text-teal-700 hover:text-teal-900 transition">
                    {{ rx.prescription_number }}
                  </Link>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ formatDate(rx.prescription_date) }}</td>
                <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                  {{ rx.patient_id ? `#${rx.patient_id}` : '—' }}
                </td>
                <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                  {{ rx.doctor_id ? `#${rx.doctor_id}` : '—' }}
                </td>
                <td class="px-4 py-3">
                  <span :class="rxStatusClass(rx.status)"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
                    {{ capitalize(rx.status) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-500">
                  {{ rx.dispensed_by ?? '—' }}
                  <span v-if="rx.dispensed_at" class="block text-slate-400">{{ rx.dispensed_at }}</span>
                </td>
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.prescriptions.show', rx.id)" icon="eye">
                      View
                    </ActionItem>
                    <ActionItem v-if="['pending','partial'].includes(rx.status)"
                                :href="route('pharmacy.sales.create') + '?prescription_id=' + rx.id"
                                icon="check">
                      Dispense
                    </ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="7" class="py-16 text-center text-slate-400">
                <div class="flex flex-col items-center gap-3">
                  <ClipboardDocumentListIcon class="w-12 h-12 opacity-30" />
                  <p class="text-sm font-medium">
                    {{ filters.status ? `No ${filters.status} prescriptions` : 'No prescriptions found' }}
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">
          Showing {{ prescriptions.from ?? 0 }}–{{ prescriptions.to ?? 0 }} of {{ prescriptions.total }}
        </p>
        <Pagination :links="prescriptions.links" />
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router }  from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import {
  PlusIcon, MagnifyingGlassIcon, ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout    from '@/Layouts/AuthenticatedLayout.vue'
import SummaryCard  from '@/Components/SummaryCard.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'

const props = defineProps({
  prescriptions: Object,
  filters:       Object,
  summary:       Object,
})

const filters = ref({ ...props.filters })
const hasActiveFilters = computed(() => Object.values(filters.value).some(v => v && v !== ''))

const statusTabs = [
  { value: '',          label: 'All' },
  { value: 'pending',   label: 'Pending' },
  { value: 'partial',   label: 'Partial' },
  { value: 'dispensed', label: 'Dispensed' },
  { value: 'cancelled', label: 'Cancelled' },
]

function applyFilters() {
  router.get(route('pharmacy.prescriptions.index'), filters.value, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function clearFilters() { filters.value = {}; applyFilters() }
function setStatus(v)   { filters.value.status = v; applyFilters() }

function rxStatusClass(s) {
  return {
    pending:   'bg-amber-50  text-amber-700  ring-1 ring-amber-200',
    partial:   'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
    dispensed: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    cancelled: 'bg-red-50    text-red-600    ring-1 ring-red-200',
  }[s] ?? 'bg-slate-100 text-slate-500'
}

function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
}
function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>