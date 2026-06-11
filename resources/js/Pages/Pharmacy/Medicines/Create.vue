<template>
  <AuthenticatedLayout title="Add Medicine">

    <!-- ── Header ─────────────────────────────────────────────────── -->
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.medicines.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Add Medicine</h1>
        <p class="text-sm text-slate-500 mt-0.5">Create a new drug master entry</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- ── Section: Identity ──────────────────────────────────────── -->
      <FormSection title="Drug Identity" description="Core medicine details and classification">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <!-- Brand Name -->
          <FormField label="Brand / Trade Name" required :error="form.errors.name" class="md:col-span-2">
            <input v-model="form.name" type="text" placeholder="e.g. Panadol, Augmentin"
                   class="form-input" :class="{ 'form-input-error': form.errors.name }" />
          </FormField>

          <!-- Generic Name -->
          <FormField label="Generic (INN) Name" required :error="form.errors.generic_id">
            <Combobox v-model="form.generic_id" :options="generics"
                      label-key="name" value-key="id"
                      placeholder="Search generic name…" />
            <template v-if="selectedGeneric?.is_controlled" #hint>
              <span class="text-red-600 text-xs flex items-center gap-1">
                <ExclamationCircleIcon class="w-3 h-3" />
                This is a controlled substance
              </span>
            </template>
          </FormField>

          <!-- Category -->
          <FormField label="Category" required :error="form.errors.medicine_category_id">
            <select v-model="form.medicine_category_id" class="form-select">
              <option value="">Select category…</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </FormField>

          <!-- Form / Dosage Form -->
          <FormField label="Dosage Form" required :error="form.errors.form">
            <div class="grid grid-cols-4 gap-2">
              <button v-for="f in forms" :key="f" type="button"
                      @click="form.form = f"
                      :class="[
                        'py-2 px-3 rounded-lg text-xs font-medium border transition-all',
                        form.form === f
                          ? 'bg-teal-600 text-white border-teal-600 shadow-sm'
                          : 'bg-white text-slate-600 border-slate-200 hover:border-teal-400 hover:text-teal-600',
                      ]">
                {{ capitalize(f) }}
              </button>
            </div>
          </FormField>

          <!-- Strength -->
          <FormField label="Strength" :error="form.errors.strength">
            <input v-model="form.strength" type="text" placeholder="e.g. 500mg, 250mg/5mL"
                   class="form-input" />
          </FormField>

          <!-- Unit -->
          <FormField label="Unit of Measure" required :error="form.errors.medicine_unit_id">
            <select v-model="form.medicine_unit_id" class="form-select">
              <option value="">Select unit…</option>
              <option v-for="unit in units" :key="unit.id" :value="unit.id">
                {{ unit.name }} ({{ unit.abbreviation }})
              </option>
            </select>
          </FormField>

          <!-- Manufacturer -->
          <FormField label="Manufacturer" :error="form.errors.manufacturer">
            <input v-model="form.manufacturer" type="text" placeholder="e.g. GlaxoSmithKline"
                   class="form-input" />
          </FormField>

          <!-- Barcode -->
          <FormField label="Barcode" :error="form.errors.barcode">
            <input v-model="form.barcode" type="text" placeholder="Scan or enter barcode"
                   class="form-input font-mono" />
          </FormField>

        </div>
      </FormSection>

      <!-- ── Section: Pricing ───────────────────────────────────────── -->
      <FormSection title="Pricing" description="Purchase cost, selling price and tax">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

          <FormField label="Purchase Price" required :error="form.errors.purchase_price">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rs</span>
              <input v-model="form.purchase_price" type="number" step="0.01" min="0"
                     class="form-input pl-9 font-mono" />
            </div>
          </FormField>

          <FormField label="Sale Price" required :error="form.errors.sale_price">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rs</span>
              <input v-model="form.sale_price" type="number" step="0.01" min="0"
                     class="form-input pl-9 font-mono" />
            </div>
            <template #hint>
              <span v-if="markupPercent !== null" class="text-xs text-slate-500">
                Markup: <span :class="markupPercent >= 0 ? 'text-emerald-600' : 'text-red-600'" class="font-semibold">
                  {{ markupPercent }}%
                </span>
              </span>
            </template>
          </FormField>

          <FormField label="MRP" :error="form.errors.mrp">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rs</span>
              <input v-model="form.mrp" type="number" step="0.01" min="0"
                     class="form-input pl-9 font-mono" />
            </div>
          </FormField>

          <FormField label="Tax %" :error="form.errors.tax_percent">
            <div class="relative">
              <input v-model="form.tax_percent" type="number" step="0.01" min="0" max="100"
                     class="form-input pr-8 font-mono" />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
            </div>
          </FormField>

        </div>
      </FormSection>

      <!-- ── Section: Stock Control ─────────────────────────────────── -->
      <FormSection title="Stock Control" description="Reorder alerts and shelf placement">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

          <FormField label="Reorder Level" required :error="form.errors.reorder_level">
            <input v-model="form.reorder_level" type="number" min="0"
                   class="form-input font-mono" />
            <template #hint>Alert fires when stock falls to or below this quantity</template>
          </FormField>

          <FormField label="Reorder Quantity" required :error="form.errors.reorder_quantity">
            <input v-model="form.reorder_quantity" type="number" min="1"
                   class="form-input font-mono" />
            <template #hint>Suggested order quantity when reorder is triggered</template>
          </FormField>

          <FormField label="Shelf / Rack Location" :error="form.errors.shelf_location">
            <input v-model="form.shelf_location" type="text" placeholder="e.g. A-12, Row 3"
                   class="form-input font-mono" />
          </FormField>

        </div>
      </FormSection>

      <!-- ── Section: Clinical Flags ────────────────────────────────── -->
      <FormSection title="Clinical Flags" description="Prescription and controlled substance settings">

        <div class="flex flex-col gap-4">

          <ToggleField v-model="form.is_prescription_required"
                       label="Prescription Required"
                       description="This medicine requires a valid prescription to dispense" />

          <ToggleField v-model="form.is_controlled"
                       label="Controlled Substance"
                       description="Mark as controlled drug — requires additional logging and approval" />

          <ToggleField v-model="form.is_active"
                       label="Active"
                       description="Inactive medicines are hidden from dispensing and purchasing" />

        </div>
      </FormSection>

      <!-- ── Section: Notes ─────────────────────────────────────────── -->
      <FormSection title="Notes" description="Internal notes for pharmacists">
        <textarea v-model="form.notes" rows="3" placeholder="Storage instructions, special handling, etc."
                  class="form-input resize-none" />
      </FormSection>

      <!-- ── Form Actions ───────────────────────────────────────────── -->
      <div class="flex items-center justify-end gap-3 pt-2 pb-6">
        <Link :href="route('pharmacy.medicines.index')"
              class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 disabled:opacity-60 active:scale-95 transition-all">
          <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
          <CheckIcon v-else class="w-4 h-4" />
          Save Medicine
        </button>
      </div>

    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, ArrowPathIcon, CheckIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import FormSection  from '@/Components/FormSection.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormField    from '@/Components/FormField.vue'
import ToggleField  from '@/Components/ToggleField.vue'
import Combobox     from '@/Components/Combobox.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  categories: Array,
  generics:   Array,
  units:      Array,
  forms:      Array,
})

// ── Form ───────────────────────────────────────────────────────────
const form = useForm({
  medicine_category_id:     '',
  generic_id:               '',
  medicine_unit_id:         '',
  name:                     '',
  strength:                 '',
  form:                     'tablet',
  manufacturer:             '',
  barcode:                  '',
  hsn_code:                 '',
  purchase_price:           0,
  sale_price:               0,
  mrp:                      '',
  tax_percent:              0,
  reorder_level:            10,
  reorder_quantity:         100,
  shelf_location:           '',
  is_prescription_required: false,
  is_controlled:            false,
  is_active:                true,
  notes:                    '',
})

// ── Computed ───────────────────────────────────────────────────────
const selectedGeneric = computed(() =>
  props.generics.find(g => g.id === form.generic_id)
)

const markupPercent = computed(() => {
  const cost = parseFloat(form.purchase_price)
  const sell = parseFloat(form.sale_price)
  if (!cost || cost <= 0) return null
  return ((sell - cost) / cost * 100).toFixed(1)
})

// ── Methods ────────────────────────────────────────────────────────
function submit() {
  form.post(route('pharmacy.medicines.store'), {
    onSuccess: () => form.reset(),
  })
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ')
}
</script>