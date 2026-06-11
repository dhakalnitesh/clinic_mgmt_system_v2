<template>
  <AuthenticatedLayout title="Drug Master">

    <!-- ── Page Header ──────────────────────────────────────────── -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Drug Master</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage medicines, pricing and stock control settings</p>
      </div>
      <Link :href="route('pharmacy.medicines.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        Add Medicine
      </Link>
    </div>

    <!-- ── Summary Cards ─────────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <SummaryCard label="Total Medicines" :value="summary.total"       icon="pills"    color="slate" />
      <SummaryCard label="Active"          :value="summary.active"      icon="check"    color="teal" />
      <SummaryCard label="Low Stock"       :value="summary.low_stock"   icon="warning"  color="amber"
                   :clickable="true" @click="setFilter('stock','low')" />
      <SummaryCard label="Near Expiry"     :value="summary.near_expiry" icon="clock"    color="red"
                   :clickable="true" @click="setFilter('expiry','near')" />
    </div>

    <!-- ── Filters Bar ────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4">
      <div class="flex flex-wrap items-center gap-3 p-4">

        <!-- Search -->
        <div class="relative flex-1 min-w-52">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          <input v-model="filters.search"
                 @input="debounceSearch"
                 type="text"
                 placeholder="Search by name, barcode, generic…"
                 class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition" />
        </div>

        <!-- Category Filter -->
        <div class="flex flex-col gap-1">
          <label for="filter-category" class="text-xs font-medium text-slate-500">Category</label>
          <select id="filter-category" v-model="filters.category" @change="applyFilters"
                  class="text-sm rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <!-- Form Filter -->
        <div class="flex flex-col gap-1">
          <label for="filter-form" class="text-xs font-medium text-slate-500">Form</label>
          <select id="filter-form" v-model="filters.form" @change="applyFilters"
                  class="text-sm rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
            <option value="">All Forms</option>
            <option v-for="form in medicineFormsList" :key="form" :value="form">{{ capitalize(form) }}</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="flex flex-col gap-1">
          <label for="filter-status" class="text-xs font-medium text-slate-500">Status</label>
          <select id="filter-status" v-model="filters.status" @change="applyFilters"
                  class="text-sm rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <!-- Stock Filter -->
        <div class="flex flex-col gap-1">
          <label for="filter-stock" class="text-xs font-medium text-slate-500">Stock</label>
          <select id="filter-stock" v-model="filters.stock" @change="applyFilters"
                  class="text-sm rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
            <option value="">All Stock</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
          </select>
        </div>

        <!-- Clear Filters -->
        <button v-if="hasActiveFilters" @click="clearFilters"
                class="text-sm text-slate-500 hover:text-slate-700 underline">
          Clear filters
        </button>

      </div>
    </div>

    <!-- ── Table ──────────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <Th field="name"           :sort="filters" @sort="sortBy">Medicine / Generic</Th>
              <Th field="form"           :sort="filters" @sort="sortBy">Form</Th>
              <Th field="sale_price"     :sort="filters" @sort="sortBy" class="text-right">Price</Th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Stock</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Nearest Expiry</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Shelf</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Flags</th>
              <th class="px-4 py-3 w-14"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="medicines.data.length">
              <tr v-for="med in medicines.data" :key="med.id"
                  class="hover:bg-slate-50/60 transition-colors group">

                <!-- Name + Generic -->
                <td class="px-4 py-3">
                  <div class="font-semibold text-slate-800">{{ med.name }}</div>
                  <div class="text-xs text-slate-400 mt-0.5">
                    {{ med.generic }} <span v-if="med.strength">· {{ med.strength }}</span>
                    <span v-if="med.category" class="ml-2 text-slate-300">{{ med.category }}</span>
                  </div>
                </td>

                <!-- Form -->
                <td class="px-4 py-3">
                  <FormBadge :form="med.form" />
                </td>

                <!-- Price -->
                <td class="px-4 py-3 text-right">
                  <div class="font-mono font-semibold text-slate-800">{{ formatCurrency(med.sale_price) }}</div>
                  <div class="text-xs text-slate-400 font-mono">Cost: {{ formatCurrency(med.purchase_price) }}</div>
                </td>

                <!-- Stock -->
                <td class="px-4 py-3">
                  <StockBadge :status="med.stock_status" :count="med.total_stock" :show-count="true" />
                </td>

                <!-- Expiry -->
                <td class="px-4 py-3">
                  <span v-if="med.nearest_expiry" class="text-xs font-mono text-slate-600">
                    {{ formatDate(med.nearest_expiry) }}
                  </span>
                  <span v-else class="text-slate-300 text-xs">No batches</span>
                </td>

                <!-- Shelf -->
                <td class="px-4 py-3">
                  <span v-if="med.shelf_location"
                        class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                    {{ med.shelf_location }}
                  </span>
                  <span v-else class="text-slate-300">—</span>
                </td>

                <!-- Flags -->
                <td class="px-4 py-3">
                  <div class="flex items-center gap-1">
                    <span v-if="med.is_prescription_required"
                          class="text-xs bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-medium"
                          title="Prescription Required">Rx</span>
                    <span v-if="med.is_controlled"
                          class="text-xs bg-red-50 text-red-600 px-1.5 py-0.5 rounded font-medium"
                          title="Controlled Substance">CD</span>
                    <span v-if="!med.is_active"
                          class="text-xs bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-medium">Inactive</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem :href="route('pharmacy.medicines.show', med.id)" icon="eye">View</ActionItem>
                    <ActionItem :href="route('pharmacy.medicines.edit', med.id)" icon="edit">Edit</ActionItem>
                    <ActionItem @click="toggleActive(med)" icon="toggle">
                      {{ med.is_active ? 'Deactivate' : 'Activate' }}
                    </ActionItem>
                    <ActionDivider />
                    <ActionItem @click="confirmDelete(med)" icon="trash" danger>Delete</ActionItem>
                  </ActionMenu>
                </td>

              </tr>
            </template>

            <!-- Empty State -->
            <tr v-else>
              <td colspan="8" class="text-center py-16">
                <div class="flex flex-col items-center gap-3 text-slate-400">
                  <BeakerIcon class="w-12 h-12 opacity-30" />
                  <div class="text-sm font-medium">No medicines found</div>
                  <div v-if="hasActiveFilters" class="text-xs">
                    Try adjusting your filters or
                    <button @click="clearFilters" class="text-teal-600 underline">clear all filters</button>
                  </div>
                  <Link v-else :href="route('pharmacy.medicines.create')"
                        class="text-xs text-teal-600 underline">
                    Add your first medicine
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
          Showing {{ medicines.from }}–{{ medicines.to }} of {{ medicines.total }} medicines
        </p>
        <Pagination :links="medicines.links" />
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-model="deleteModal.show"
      title="Delete Medicine"
      :message="`Are you sure you want to delete '${deleteModal.medicine?.name}'? This cannot be undone.`"
      confirm-label="Delete"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="deleteMedicine"
    />

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import {
  PlusIcon, MagnifyingGlassIcon, BeakerIcon,
} from '@heroicons/vue/24/outline'
import StockBadge   from '@/Components/Pharmacy/StockBadge.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ExpiryTag    from '@/Components/Pharmacy/ExpiryTag.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionDivider from '@/Components/ActionDivider.vue'
import Th           from '@/Components/SortableTh.vue'
import SummaryCard  from '@/Components/SummaryCard.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import FormBadge    from '@/Components/Pharmacy/FormBadge.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  medicines:  Object,
  categories: Array,
  filters:    Object,
  summary:    Object,
})

// ── State ──────────────────────────────────────────────────────────
const filters = ref(
  Object.fromEntries(
    Object.entries(props.filters).map(([k, v]) => [k, v ?? ''])
  )
)

const deleteModal = ref({ show: false, medicine: null })

const medicineFormsList = [
  'tablet','capsule','syrup','suspension','injection',
  'cream','ointment','gel','drops','inhaler','patch',
  'suppository','powder','lotion','solution','other',
]

// ── Computed ───────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  Object.values(filters.value).some(v => v && v !== '')
)

// ── Methods ────────────────────────────────────────────────────────
function applyFilters() {
  router.get(route('pharmacy.medicines.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

const debounceSearch = useDebounceFn(applyFilters, 350)

function clearFilters() {
  filters.value = {}
  applyFilters()
}

function setFilter(key, value) {
  filters.value[key] = value
  applyFilters()
}

function sortBy(field) {
  if (filters.value.sort === field) {
    filters.value.direction = filters.value.direction === 'asc' ? 'desc' : 'asc'
  } else {
    filters.value.sort = field
    filters.value.direction = 'asc'
  }
  applyFilters()
}

function toggleActive(med) {
  router.patch(route('pharmacy.medicines.toggle-active', med.id), {}, {
    preserveScroll: true,
  })
}

function confirmDelete(med) {
  deleteModal.value = { show: true, medicine: med }
}

function deleteMedicine() {
  router.delete(route('pharmacy.medicines.destroy', deleteModal.value.medicine.id), {
    onSuccess: () => { deleteModal.value = { show: false, medicine: null } },
  })
}

// ── Formatters ─────────────────────────────────────────────────────
function formatCurrency(val) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(val)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ')
}
</script>