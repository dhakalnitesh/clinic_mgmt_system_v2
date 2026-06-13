<template>
  <AuthenticatedLayout title="Prescriptions">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Prescriptions</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage patient prescriptions</p>
      </div>
      <button @click="showCreateModal = true"
              class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        New Prescription
      </button>
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
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex flex-wrap items-end gap-3">
      <div class="relative flex-1 min-w-48">
        <label class="text-xs font-medium text-slate-500 mb-1 block">Search</label>
        <MagnifyingGlassIcon class="absolute left-3 top-9 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
        <input v-model="search" @input="debounceSearch" type="text"
               placeholder="Rx number, patient, doctor…"
               class="form-input pl-9" />
      </div>
      <div class="w-44">
        <label class="text-xs font-medium text-slate-500 mb-1 block">From Date (BS)</label>
        <NepaliDatepicker v-model="fromDate" placeholder="Start date" />
      </div>
      <div class="w-44">
        <label class="text-xs font-medium text-slate-500 mb-1 block">To Date (BS)</label>
        <NepaliDatepicker v-model="toDate" placeholder="End date" />
      </div>
      <div class="w-24">
        <label class="text-xs font-medium text-slate-500 mb-1 block">Per page</label>
        <select v-model="perPage" @change="applyFilters"
                class="form-select text-sm">
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
      <button @click="resetFilters"
              class="px-4 py-2 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition self-end">
        Reset
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Rx Number</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date (BS)</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Patient</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Doctor</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>

              <th class="px-4 py-3 w-12">Action</th>
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
                <td class="px-4 py-3 text-slate-600">
                  {{ rx.created_at_bs ?? formatDate(rx.prescribed_at) }}
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">{{ rx.patient_name ?? '—' }}</div>
                  <div class="text-xs text-slate-400">{{ rx.patient_id ? `#${rx.patient_id}` : '' }}</div>
                </td>
                <td class="px-4 py-3 text-slate-600">
                  {{ rx.doctor_name ? `Dr. ${rx.doctor_name}` : '—' }}
                </td>
                <td class="px-4 py-3">
                  <span :class="statusClass(rx.status)"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
                    {{ capitalize(rx.status) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.prescriptions.show', rx.id)" icon="eye">
                      View
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

    <CreatePrescriptionModal v-if="showCreateModal" :patients="patients" :doctors="doctors" :generics="generics" :today-bs="today_bs"
      @close="showCreateModal = false" @success="refreshData" />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import {
  PlusIcon, MagnifyingGlassIcon, ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import NepaliDatepicker from '@/Components/NepaliDatepicker.vue'
import CreatePrescriptionModal from './CreateModal.vue'

const props = defineProps({
  prescriptions: Object,
  filters:       Object,
  summary:       Object,
  patients:      Array,
  doctors:       Array,
  generics:      Array,
  today_bs:      String,
})

const showCreateModal = ref(false)

const search   = ref(props.filters?.search ?? '')
const fromDate = ref(props.filters?.from_date ?? '')
const toDate   = ref(props.filters?.to_date ?? '')
const perPage  = ref(props.filters?.per_page ?? 10)

function applyFilters() {
  router.get(route('pharmacy.prescriptions.index'), {
    search:   search.value || '',
    from_date: fromDate.value || '',
    to_date:  toDate.value || '',
    per_page: perPage.value,
    status:   filters.value.status || '',
  }, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function resetFilters() {
  search.value = ''; fromDate.value = ''; toDate.value = ''; perPage.value = 10
  filters.value.status = ''
  applyFilters()
}
function setStatus(v) { filters.value.status = v; applyFilters() }
function refreshData() { router.reload({ only: ['prescriptions', 'summary'] }) }

const filters = ref({
  status: props.filters?.status ?? '',
})

const statusTabs = [
  { value: '',          label: 'All' },
  { value: 'pending',   label: 'Pending' },
  { value: 'partial',   label: 'Partial' },
  { value: 'dispensed', label: 'Dispensed' },
  { value: 'cancelled', label: 'Cancelled' },
]

function statusClass(s) {
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
  return d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : ''
}
</script>
