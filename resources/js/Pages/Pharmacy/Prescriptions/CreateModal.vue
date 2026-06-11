<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
    <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl">
      <!-- Header -->
      <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-bold text-indigo-600 flex items-center gap-2">
          <i class="fas fa-prescription"></i>
          New Prescription
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
      </div>

      <div class="px-6 py-4 max-h-[70vh] overflow-y-auto space-y-4">

        <!-- General errors -->
        <div v-if="errors.patient_id || errors.doctor_id || errors.prescription_date || errors.items || errors.error"
             class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
          <p v-if="errors.error" class="text-sm text-red-600">{{ errors.error }}</p>
          <p v-if="errors.patient_id" class="text-sm text-red-600">{{ errors.patient_id }}</p>
          <p v-if="errors.doctor_id" class="text-sm text-red-600">{{ errors.doctor_id }}</p>
          <p v-if="errors.prescription_date" class="text-sm text-red-600">{{ errors.prescription_date }}</p>
          <p v-if="errors.items" class="text-sm text-red-600">{{ errors.items }}</p>
        </div>

        <!-- Date / Patient / Doctor -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700">Date (BS) <span class="text-red-500">*</span></label>
            <NepaliDatepicker v-model="form.prescription_date" placeholder="Select date" />
            <p v-if="form.errors.prescription_date" class="text-xs text-red-500 mt-0.5">{{ form.errors.prescription_date }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Doctor <span class="text-red-500">*</span></label>
            <select v-model="form.doctor_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"
                    :class="{ 'border-red-400': form.errors.doctor_id }">
              <option value="">Select Doctor</option>
              <option v-for="d in doctors" :key="d.id" :value="d.id">Dr. {{ d.name }} — {{ d.specialization || 'N/A' }}</option>
            </select>
            <p v-if="form.errors.doctor_id" class="text-xs text-red-500 mt-0.5">{{ form.errors.doctor_id }}</p>
          </div>
          <div class="md:col-span-2">
            <div class="flex items-center justify-between">
              <label class="text-sm font-medium text-gray-700">Patient <span class="text-red-500">*</span></label>
              <button type="button" @click="showPatientModal = true"
                      class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-medium transition">
                <i class="fas fa-plus"></i> Patient
              </button>
            </div>
            <select v-model="form.patient_id"
                    class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"
                    :class="{ 'border-red-400': form.errors.patient_id }">
              <option value="">Select Patient</option>
              <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }} — {{ p.phone || 'N/A' }}</option>
            </select>
            <p v-if="form.errors.patient_id" class="text-xs text-red-500 mt-0.5">{{ form.errors.patient_id }}</p>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="text-sm font-medium text-gray-700">Notes</label>
          <textarea v-model="form.notes" rows="2" placeholder="Optional notes…"
                    class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition resize-none"></textarea>
        </div>

        <!-- Medicines -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">Medicines <span class="text-red-500">*</span></label>

          <!-- Add medicine row: search + manual -->
          <div class="flex gap-2">
            <div class="flex-1">
              <DrugSearchInput
                placeholder="Search medicine…"
                :search-url="route('pharmacy.medicines.search')"
                @select="addItem"
              />
            </div>
            <div class="flex-1 flex gap-1">
              <input v-model="manualMedicineName" type="text" placeholder="Or type name…"
                     @keyup.enter="addManualItem"
                     class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
              <button type="button" @click="addManualItem"
                      :disabled="!manualMedicineName.trim()"
                      class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 shrink-0 transition">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <p v-if="form.errors.items" class="text-xs text-red-600 mt-1">{{ form.errors.items }}</p>

          <div class="space-y-2 mt-3">
            <div v-for="(item, idx) in form.items" :key="item._key"
                 class="border border-gray-200 rounded-lg overflow-hidden">
              <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center gap-2 min-w-0">
                  <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
                  <span class="font-medium text-gray-800 text-sm truncate">{{ item.medicine_name || 'Manual entry' }}</span>
                  <span v-if="item.strength" class="text-xs text-gray-500">{{ item.strength }}</span>
                </div>
                <button type="button" @click="removeItem(idx)" class="text-gray-400 hover:text-red-500 text-lg leading-none">&times;</button>
              </div>
              <div class="p-3 grid grid-cols-4 gap-2">
                <div>
                  <label class="text-xs text-gray-500">Qty <span class="text-red-500">*</span></label>
                  <input v-model.number="item.quantity_prescribed" type="number" min="1"
                         class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm font-mono focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none"
                         :class="{ 'border-red-400': form.errors[`items.${idx}.quantity_prescribed`] }" />
                  <p v-if="form.errors[`items.${idx}.quantity_prescribed`]" class="text-xs text-red-500">{{ form.errors[`items.${idx}.quantity_prescribed`] }}</p>
                </div>
                <div>
                  <label class="text-xs text-gray-500">Freq</label>
                  <select v-model="item.frequency"
                          class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none">
                    <option value="">—</option>
                    <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                  </select>
                </div>
                <div>
                  <label class="text-xs text-gray-500">Days</label>
                  <input v-model.number="item.duration_days" type="number" min="1"
                         class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm font-mono focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none" />
                </div>
                <div>
                  <label class="text-xs text-gray-500">Route</label>
                  <select v-model="item.route"
                          class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none">
                    <option value="oral">Oral</option>
                    <option value="topical">Topical</option>
                    <option value="iv">IV</option>
                    <option value="im">IM</option>
                  </select>
                </div>
                <div class="col-span-4">
                  <label class="text-xs text-gray-500">Dosage</label>
                  <input v-model="item.dosage_instruction" type="text" placeholder="e.g. 1 tablet twice daily"
                         class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none" />
                </div>
              </div>
            </div>
          </div>

          <div v-if="!form.items.length"
               class="border-2 border-dashed border-gray-200 rounded-lg py-8 text-center text-gray-400 mt-2">
            <i class="fas fa-pills text-2xl mb-1 opacity-40"></i>
            <p class="text-sm">Search or type a medicine name above</p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200">
        <button type="button" @click="$emit('close')"
                class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-medium transition">
          Cancel
        </button>
        <button type="button" :disabled="form.processing" @click="submit"
                class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 text-sm font-medium transition">
          <i v-if="form.processing" class="fas fa-spinner fa-spin mr-1"></i>
          {{ form.processing ? 'Saving…' : 'Save Prescription' }}
        </button>
      </div>
    </div>

    <CreatePatientModal v-if="showPatientModal" @close="showPatientModal = false" @created="addPatient" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import DrugSearchInput from '@/Components/Pharmacy/DrugSearchInput.vue'
import NepaliDatepicker from '@/Components/NepaliDatepicker.vue'
import CreatePatientModal from '@/Pages/Patients/CreateModal.vue'

const props = defineProps({
  patients: Array,
  doctors: Array,
  generics: Array,
  todayBs: { type: String, default: '' },
})

const emit = defineEmits(['close', 'success'])
const errors = usePage().props.errors

const showPatientModal = ref(false)
const manualMedicineName = ref('')

let keyCounter = 0

const frequencies = [
  { value: 'once_daily',       label: 'Once daily' },
  { value: 'twice_daily',      label: 'Twice daily' },
  { value: 'thrice_daily',     label: 'Thrice daily' },
  { value: 'four_times_daily', label: '4 times daily' },
  { value: 'every_6_hours',    label: 'Every 6 hrs' },
  { value: 'every_8_hours',    label: 'Every 8 hrs' },
  { value: 'every_12_hours',   label: 'Every 12 hrs' },
  { value: 'at_bedtime',       label: 'Bedtime' },
  { value: 'before_meals',     label: 'Before meals' },
  { value: 'after_meals',      label: 'After meals' },
  { value: 'as_needed',        label: 'PRN' },
]

const form = useForm({
  prescription_date: props.todayBs || '',
  patient_id:        '',
  doctor_id:         '',
  encounter_id:      '',
  notes:             '',
  items:             [],
})

function addItem(med) {
  if (form.items.find(i => i.medicine_id === med.id)) return
  form.items.push({
    _key: keyCounter++,
    medicine_id: med.id,
    medicine_name: med.name,
    strength: med.strength,
    generic_id: null,
    dosage_instruction: '',
    frequency: 'twice_daily',
    duration_days: 7,
    route: 'oral',
    quantity_prescribed: 14,
    is_substitutable: true,
    instructions: '',
  })
}

function addManualItem() {
  const name = manualMedicineName.value.trim()
  if (!name) return
  form.items.push({
    _key: keyCounter++,
    medicine_id: null,
    medicine_name: name,
    strength: null,
    generic_id: null,
    dosage_instruction: '',
    frequency: 'twice_daily',
    duration_days: 7,
    route: 'oral',
    quantity_prescribed: 14,
    is_substitutable: true,
    instructions: '',
  })
  manualMedicineName.value = ''
}

function removeItem(idx) { form.items.splice(idx, 1) }

function addPatient(patient) {
  form.patient_id = patient.id
  showPatientModal.value = false
}

function submit() {
  form.transform(data => ({
    ...data,
    items: data.items.map(({ _key, strength, form: f, ...rest }) => rest),
  })).post(route('pharmacy.prescriptions.store'), {
    preserveScroll: true,
    onSuccess: () => emit('success'),
  })
}
</script>
