<template>
  <AuthenticatedLayout :title="`Edit — ${medicine.name}`">

    <!-- ── Header ─────────────────────────────────────────────────── -->
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.medicines.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Medicine</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ medicine.name }}</p>
      </div>

      <!-- Quick status toggle at top right -->
      <div class="ml-auto flex items-center gap-2">
        <span class="text-sm text-slate-500">Active</span>
        <Toggle v-model="form.is_active" />
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- ── Section: Identity ──────────────────────────────────────── -->
      <FormSection title="Drug Identity" description="Core medicine details and classification">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <FormField label="Brand / Trade Name" required :error="form.errors.name" class="md:col-span-2">
            <input v-model="form.name" type="text" class="form-input" />
          </FormField>

          <FormField label="Generic (INN) Name" required :error="form.errors.generic_id">
            <Combobox v-model="form.generic_id" :options="generics" label-key="name" value-key="id" />
          </FormField>

          <FormField label="Category" required :error="form.errors.medicine_category_id">
            <select v-model="form.medicine_category_id" class="form-select">
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </FormField>

          <FormField label="Dosage Form" required :error="form.errors.form">
            <div class="grid grid-cols-4 gap-2">
              <button v-for="f in forms" :key="f" type="button"
                      @click="form.form = f"
                      :class="[
                        'py-2 px-3 rounded-lg text-xs font-medium border transition-all',
                        form.form === f
                          ? 'bg-teal-600 text-white border-teal-600 shadow-sm'
                          : 'bg-white text-slate-600 border-slate-200 hover:border-teal-400',
                      ]">
                {{ capitalize(f) }}
              </button>
            </div>
          </FormField>

          <FormField label="Strength" :error="form.errors.strength">
            <input v-model="form.strength" type="text" class="form-input" />
          </FormField>

          <FormField label="Unit of Measure" required :error="form.errors.medicine_unit_id">
            <select v-model="form.medicine_unit_id" class="form-select">
              <option v-for="unit in units" :key="unit.id" :value="unit.id">
                {{ unit.name }} ({{ unit.abbreviation }})
              </option>
            </select>
          </FormField>

          <FormField label="Manufacturer" :error="form.errors.manufacturer">
            <input v-model="form.manufacturer" type="text" class="form-input" />
          </FormField>

          <FormField label="Barcode" :error="form.errors.barcode">
            <input v-model="form.barcode" type="text" class="form-input font-mono" />
          </FormField>

        </div>
      </FormSection>

      <!-- ── Section: Pricing ───────────────────────────────────────── -->
      <FormSection title="Pricing" description="Purchase cost, selling price and tax">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

          <FormField label="Purchase Price" required :error="form.errors.purchase_price">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rs</span>
              <input v-model="form.purchase_price" type="number" step="0.01" min="0" class="form-input pl-9 font-mono" />
            </div>
          </FormField>

          <FormField label="Sale Price" required :error="form.errors.sale_price">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rs</span>
              <input v-model="form.sale_price" type="number" step="0.01" min="0" class="form-input pl-9 font-mono" />
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
              <input v-model="form.mrp" type="number" step="0.01" min="0" class="form-input pl-9 font-mono" />
            </div>
          </FormField>

          <FormField label="Tax %" :error="form.errors.tax_percent">
            <div class="relative">
              <input v-model="form.tax_percent" type="number" step="0.01" min="0" max="100" class="form-input pr-8 font-mono" />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
            </div>
          </FormField>

        </div>
      </FormSection>

      <!-- ── Section: Stock Control ─────────────────────────────────── -->
      <FormSection title="Stock Control" description="Reorder alerts and shelf placement">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

          <FormField label="Reorder Level" required :error="form.errors.reorder_level">
            <input v-model="form.reorder_level" type="number" min="0" class="form-input font-mono" />
          </FormField>

          <FormField label="Reorder Quantity" required :error="form.errors.reorder_quantity">
            <input v-model="form.reorder_quantity" type="number" min="1" class="form-input font-mono" />
          </FormField>

          <FormField label="Shelf / Rack Location" :error="form.errors.shelf_location">
            <input v-model="form.shelf_location" type="text" class="form-input font-mono" />
          </FormField>

        </div>
      </FormSection>

      <!-- ── Section: Clinical Flags ────────────────────────────────── -->
      <FormSection title="Clinical Flags">
        <div class="flex flex-col gap-4">
          <ToggleField v-model="form.is_prescription_required"
                       label="Prescription Required"
                       description="Requires a valid prescription to dispense" />
          <ToggleField v-model="form.is_controlled"
                       label="Controlled Substance"
                       description="Controlled drug — requires additional logging and approval" />
        </div>
      </FormSection>

      <!-- ── Section: Notes ─────────────────────────────────────────── -->
      <FormSection title="Notes">
        <textarea v-model="form.notes" rows="3" class="form-input resize-none" />
      </FormSection>

      <!-- ── Dirty Warning ─────────────────────────────────────────── -->
      <div v-if="form.isDirty"
           class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
        <ExclamationTriangleIcon class="w-4 h-4 shrink-0" />
        You have unsaved changes
      </div>

      <!-- ── Form Actions ───────────────────────────────────────────── -->
      <div class="flex items-center justify-between gap-3 pt-2 pb-6">
        <button type="button" @click="confirmDelete"
                class="text-sm text-red-600 hover:text-red-800 hover:underline flex items-center gap-1">
          <TrashIcon class="w-4 h-4" />
          Delete medicine
        </button>
        <div class="flex items-center gap-3">
          <Link :href="route('pharmacy.medicines.index')"
                class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing || !form.isDirty"
                  class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 disabled:opacity-50 active:scale-95 transition-all">
            <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
            <CheckIcon v-else class="w-4 h-4" />
            Save Changes
          </button>
        </div>
      </div>

    </form>

    <!-- Delete Modal -->
    <ConfirmModal
      v-model="showDeleteModal"
      title="Delete Medicine"
      :message="`Delete '${medicine.name}'? This cannot be undone.`"
      confirm-label="Delete"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="deleteMedicine"
    />

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, ArrowPathIcon, CheckIcon,
  TrashIcon, ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormSection  from '@/Components/FormSection.vue'
import FormField    from '@/Components/FormField.vue'
import ToggleField  from '@/Components/ToggleField.vue'
import Toggle       from '@/Components/Toggle.vue'
import Combobox     from '@/Components/Combobox.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({
  medicine:   Object,
  categories: Array,
  generics:   Array,
  units:      Array,
  forms:      Array,
})

const showDeleteModal = ref(false)

const form = useForm({
  medicine_category_id:     props.medicine.medicine_category_id,
  generic_id:               props.medicine.generic_id,
  medicine_unit_id:         props.medicine.medicine_unit_id,
  name:                     props.medicine.name,
  strength:                 props.medicine.strength ?? '',
  form:                     props.medicine.form,
  manufacturer:             props.medicine.manufacturer ?? '',
  barcode:                  props.medicine.barcode ?? '',
  hsn_code:                 props.medicine.hsn_code ?? '',
  purchase_price:           props.medicine.purchase_price,
  sale_price:               props.medicine.sale_price,
  mrp:                      props.medicine.mrp ?? '',
  tax_percent:              props.medicine.tax_percent,
  reorder_level:            props.medicine.reorder_level,
  reorder_quantity:         props.medicine.reorder_quantity,
  shelf_location:           props.medicine.shelf_location ?? '',
  is_prescription_required: props.medicine.is_prescription_required,
  is_controlled:            props.medicine.is_controlled,
  is_active:                props.medicine.is_active,
  notes:                    props.medicine.notes ?? '',
})

const markupPercent = computed(() => {
  const cost = parseFloat(form.purchase_price)
  const sell = parseFloat(form.sale_price)
  if (!cost || cost <= 0) return null
  return ((sell - cost) / cost * 100).toFixed(1)
})

function submit() {
  form.put(route('pharmacy.medicines.update', props.medicine.id))
}

function confirmDelete() { showDeleteModal.value = true }

function deleteMedicine() {
  router.delete(route('pharmacy.medicines.destroy', props.medicine.id))
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ')
}
</script>