<template>
  <AuthenticatedLayout title="New Prescription">
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.prescriptions.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">New Prescription</h1>
        <p class="text-sm text-slate-500 mt-0.5">Create a manual prescription entry</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- Header -->
      <FormSection title="Prescription Details">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <FormField label="Prescription Date" required :error="form.errors.prescription_date">
            <input v-model="form.prescription_date" type="date" class="form-input" />
          </FormField>
          <FormField label="Patient ID" :error="form.errors.patient_id">
            <input v-model="form.patient_id" type="text" class="form-input font-mono"
                   placeholder="Patient reference ID" />
          </FormField>
          <FormField label="Doctor ID" :error="form.errors.doctor_id">
            <input v-model="form.doctor_id" type="text" class="form-input font-mono"
                   placeholder="Doctor reference ID" />
          </FormField>
          <FormField label="Notes" :error="form.errors.notes" class="md:col-span-3">
            <textarea v-model="form.notes" rows="2" class="form-input resize-none"
                      placeholder="Optional prescription notes…" />
          </FormField>
        </div>
      </FormSection>

      <!-- Items -->
      <FormSection title="Prescribed Medicines"
                   description="Add medicines with dosage instructions">

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Add Medicine</label>
          <DrugSearchInput
            placeholder="Search medicine to prescribe…"
            :search-url="route('pharmacy.medicines.search')"
            @select="addItem"
          />
        </div>

        <p v-if="form.errors.items" class="mb-3 text-xs text-red-600">{{ form.errors.items }}</p>

        <div class="space-y-4">
          <div v-for="(item, idx) in form.items" :key="item._key"
               class="border border-slate-200 rounded-xl overflow-hidden">

            <!-- Item Header -->
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold
                             flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
                <div>
                  <span class="font-semibold text-slate-800">{{ item.medicine_name }}</span>
                  <span v-if="item.strength" class="ml-1.5 text-xs text-slate-500">{{ item.strength }}</span>
                  <FormBadge v-if="item.form" :form="item.form" size="xs" class="ml-1.5" />
                </div>
              </div>
              <button type="button" @click="removeItem(idx)"
                      class="p-1.5 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 transition">
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>

            <!-- Fields -->
            <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-4">
              <FormField label="Qty Prescribed" required
                         :error="form.errors[`items.${idx}.quantity_prescribed`]">
                <input v-model.number="item.quantity_prescribed" type="number" min="1"
                       class="form-input font-mono text-right" />
              </FormField>

              <FormField label="Frequency" :error="form.errors[`items.${idx}.frequency`]">
                <select v-model="item.frequency" class="form-select">
                  <option value="">Select…</option>
                  <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                </select>
              </FormField>

              <FormField label="Duration (days)" :error="form.errors[`items.${idx}.duration_days`]">
                <input v-model.number="item.duration_days" type="number" min="1"
                       class="form-input font-mono" />
              </FormField>

              <FormField label="Route" :error="form.errors[`items.${idx}.route`]">
                <select v-model="item.route" class="form-select">
                  <option value="">Select…</option>
                  <option value="oral">Oral</option>
                  <option value="topical">Topical</option>
                  <option value="iv">IV</option>
                  <option value="im">IM</option>
                  <option value="sc">SC</option>
                  <option value="sublingual">Sublingual</option>
                  <option value="inhaled">Inhaled</option>
                  <option value="rectal">Rectal</option>
                  <option value="ophthalmic">Ophthalmic</option>
                  <option value="otic">Otic</option>
                </select>
              </FormField>

              <FormField label="Dosage Instructions" class="md:col-span-3"
                         :error="form.errors[`items.${idx}.dosage_instruction`]">
                <input v-model="item.dosage_instruction" type="text" class="form-input"
                       placeholder="e.g. 1 tablet twice daily after meals" />
              </FormField>

              <FormField label="Substitutable">
                <div class="flex items-center gap-2 mt-1">
                  <Toggle v-model="item.is_substitutable" label="Generic substitution allowed" />
                  <span class="text-xs text-slate-500">Generic OK</span>
                </div>
              </FormField>

              <FormField label="Special Instructions" class="md:col-span-4"
                         :error="form.errors[`items.${idx}.instructions`]">
                <input v-model="item.instructions" type="text" class="form-input"
                       placeholder="e.g. Avoid alcohol, take with food…" />
              </FormField>
            </div>
          </div>
        </div>

        <div v-if="!form.items.length"
             class="rounded-xl border-2 border-dashed border-slate-200 py-12 text-center text-slate-400">
          <ClipboardDocumentListIcon class="w-8 h-8 mx-auto mb-2 opacity-40" />
          <p class="text-sm">Search above to add medicines</p>
        </div>

      </FormSection>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-2 pb-6">
        <Link :href="route('pharmacy.prescriptions.index')"
              class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing || !form.items.length"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 disabled:opacity-60 active:scale-95 transition-all">
          <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
          <CheckIcon v-else class="w-4 h-4" />
          Save Prescription
        </button>
      </div>

    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon, ArrowPathIcon, CheckIcon, XMarkIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'
import FormSection    from '@/Components/FormSection.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormField      from '@/Components/FormField.vue'
import Toggle         from '@/Components/Toggle.vue'
import DrugSearchInput from '@/Components/Pharmacy/DrugSearchInput.vue'
import FormBadge      from '@/Components/Pharmacy/FormBadge.vue'

defineProps({ today: String, generics: Array })

let keyCounter = 0

const frequencies = [
  { value: 'once_daily',       label: 'Once daily' },
  { value: 'twice_daily',      label: 'Twice daily' },
  { value: 'thrice_daily',     label: 'Three times daily' },
  { value: 'four_times_daily', label: 'Four times daily' },
  { value: 'every_6_hours',    label: 'Every 6 hours' },
  { value: 'every_8_hours',    label: 'Every 8 hours' },
  { value: 'every_12_hours',   label: 'Every 12 hours' },
  { value: 'at_bedtime',       label: 'At bedtime' },
  { value: 'before_meals',     label: 'Before meals' },
  { value: 'after_meals',      label: 'After meals' },
  { value: 'as_needed',        label: 'As needed (PRN)' },
]

const form = useForm({
  prescription_date: new Date().toISOString().split('T')[0],
  patient_id:        '',
  doctor_id:         '',
  encounter_id:      '',
  notes:             '',
  items:             [],
})

function addItem(med) {
  if (form.items.find(i => i.medicine_id === med.id)) return
  form.items.push({
    _key:                keyCounter++,
    medicine_id:         med.id,
    medicine_name:       med.name,
    strength:            med.strength,
    form:                med.form,
    generic_id:          null,
    dosage_instruction:  '',
    frequency:           'twice_daily',
    duration_days:       7,
    route:               'oral',
    quantity_prescribed: 14,
    is_substitutable:    true,
    instructions:        '',
  })
}

function removeItem(idx) { form.items.splice(idx, 1) }

function submit() {
  form.transform(data => ({
    ...data,
    items: data.items.map(({ _key, medicine_name, strength, form: f, ...rest }) => rest),
  })).post(route('pharmacy.prescriptions.store'))
}
</script>