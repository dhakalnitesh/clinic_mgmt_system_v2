<template>
  <AuthenticatedLayout :title="isEditing ? `Edit — ${supplier.name}` : 'Add Supplier'">

    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.suppliers.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          {{ isEditing ? 'Edit Supplier' : 'Add Supplier' }}
        </h1>
        <p v-if="isEditing" class="text-sm text-slate-500 mt-0.5">{{ supplier.name }}</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- Basic Info -->
      <FormSection title="Basic Information" description="Supplier name and contact details">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <FormField label="Supplier Name" required :error="form.errors.name" class="md:col-span-2">
            <input v-model="form.name" type="text" placeholder="e.g. Nepal Pharma Distributors Pvt. Ltd." class="form-input" />
          </FormField>
          <FormField label="Contact Person" :error="form.errors.contact_person">
            <input v-model="form.contact_person" type="text" class="form-input" />
          </FormField>
          <FormField label="Phone" :error="form.errors.phone">
            <input v-model="form.phone" type="text" class="form-input" />
          </FormField>
          <FormField label="Alternate Phone" :error="form.errors.alternate_phone">
            <input v-model="form.alternate_phone" type="text" class="form-input" />
          </FormField>
          <FormField label="Email" :error="form.errors.email">
            <input v-model="form.email" type="email" class="form-input" />
          </FormField>
          <FormField label="Address" :error="form.errors.address" class="md:col-span-2">
            <textarea v-model="form.address" rows="2" class="form-input resize-none" />
          </FormField>
          <FormField label="City" :error="form.errors.city">
            <input v-model="form.city" type="text" class="form-input" />
          </FormField>
          <FormField label="State / Province" :error="form.errors.state">
            <input v-model="form.state" type="text" class="form-input" />
          </FormField>
          <FormField label="Country" :error="form.errors.country">
            <input v-model="form.country" type="text" class="form-input" />
          </FormField>
          <FormField label="Postal Code" :error="form.errors.postal_code">
            <input v-model="form.postal_code" type="text" class="form-input" />
          </FormField>
        </div>
      </FormSection>

      <!-- Regulatory -->
      <FormSection title="Regulatory & Tax" description="Drug license and tax registration">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <FormField label="Drug License No." :error="form.errors.drug_license_no">
            <input v-model="form.drug_license_no" type="text" class="form-input font-mono" />
          </FormField>
          <FormField label="License Expiry Date" :error="form.errors.drug_license_expiry">
            <input v-model="form.drug_license_expiry" type="date" class="form-input" />
          </FormField>
          <FormField label="PAN / VAT No." :error="form.errors.pan_vat_no">
            <input v-model="form.pan_vat_no" type="text" class="form-input font-mono" />
          </FormField>
        </div>
      </FormSection>

      <!-- Financial -->
      <FormSection title="Financial Terms" description="Credit terms and opening balance">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <FormField label="Credit Days" required :error="form.errors.credit_days">
            <input v-model="form.credit_days" type="number" min="0" max="365" class="form-input font-mono" />
          </FormField>
          <FormField label="Credit Limit (Rs)" required :error="form.errors.credit_limit">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rs</span>
              <input v-model="form.credit_limit" type="number" min="0" step="0.01" class="form-input pl-9 font-mono" />
            </div>
          </FormField>
          <FormField label="Opening Balance (Rs)" required :error="form.errors.opening_balance">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rs</span>
              <input v-model="form.opening_balance" type="number" step="0.01" class="form-input pl-9 font-mono" />
            </div>
          </FormField>
        </div>
      </FormSection>

      <!-- Notes & Status -->
      <FormSection title="Notes & Status">
        <div class="space-y-4">
          <FormField label="Notes" :error="form.errors.notes">
            <textarea v-model="form.notes" rows="2" class="form-input resize-none" />
          </FormField>
          <ToggleField v-model="form.is_active" label="Active" description="Inactive suppliers are hidden from purchase order creation" />
        </div>
      </FormSection>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-2 pb-6">
        <Link :href="route('pharmacy.suppliers.index')"
              class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 disabled:opacity-60 active:scale-95 transition-all">
          <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
          <CheckIcon v-else class="w-4 h-4" />
          {{ isEditing ? 'Save Changes' : 'Save Supplier' }}
        </button>
      </div>

    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon, ArrowPathIcon, CheckIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormSection from '@/Components/FormSection.vue'
import FormField   from '@/Components/FormField.vue'
import ToggleField from '@/Components/ToggleField.vue'

const props = defineProps({ supplier: { type: Object, default: null } })

const isEditing = computed(() => !!props.supplier)

const form = useForm({
  name:                 props.supplier?.name                 ?? '',
  contact_person:       props.supplier?.contact_person       ?? '',
  phone:                props.supplier?.phone                ?? '',
  alternate_phone:      props.supplier?.alternate_phone      ?? '',
  email:                props.supplier?.email                ?? '',
  address:              props.supplier?.address              ?? '',
  city:                 props.supplier?.city                 ?? '',
  state:                props.supplier?.state                ?? '',
  country:              props.supplier?.country              ?? 'Nepal',
  postal_code:          props.supplier?.postal_code          ?? '',
  drug_license_no:      props.supplier?.drug_license_no      ?? '',
  drug_license_expiry:  props.supplier?.drug_license_expiry  ?? '',
  pan_vat_no:           props.supplier?.pan_vat_no           ?? '',
  credit_days:          props.supplier?.credit_days          ?? 30,
  credit_limit:         props.supplier?.credit_limit         ?? 0,
  opening_balance:      props.supplier?.opening_balance      ?? 0,
  is_active:            props.supplier?.is_active            ?? true,
  notes:                props.supplier?.notes                ?? '',
})

function submit() {
  if (isEditing.value) {
    form.put(route('pharmacy.suppliers.update', props.supplier.id))
  } else {
    form.post(route('pharmacy.suppliers.store'))
  }
}
</script>