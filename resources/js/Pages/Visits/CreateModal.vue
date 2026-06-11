<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import CreatePatientModal from '../Patients/CreateModal.vue'

const props = defineProps({
  patients: Array,
  doctors: Array,
})

const emit = defineEmits(['close'])

const errors = usePage().props.errors
const showPatientModal = ref(false)

const form = useForm({
  patient_id: '',
  doctor_id: '',
  consultation_fee: '',
  chief_complaint: '',
  notes: '',
})

const addPatient = (patient) => {
  props.patients.push(patient)
  form.patient_id = patient.id
  showPatientModal.value = false
}

const submit = () => {
  form.post(route('visits.store'), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>
<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl p-6">
      <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">
        <h2 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
          <i class="fas fa-walk"></i>
          Create Visit
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
      </div>

      <p v-if="errors.patient_id" class="text-red-500 text-sm mb-2">{{ errors.patient_id }}</p>
      <p v-if="errors.error" class="text-red-500 text-sm mb-2">{{ errors.error }}</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="flex items-end justify-between gap-2">
          <div class="flex-1">
            <label class="text-sm font-medium text-gray-700">Patient</label>
            <select v-model="form.patient_id"
              class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
              <option value="">Select Patient</option>
              <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }} - {{ p.phone || 'N/A' }}</option>
            </select>
            <p v-if="form.errors.patient_id" class="text-sm text-red-500 mt-1">{{ form.errors.patient_id }}</p>
          </div>
          <button type="button" @click="showPatientModal = true"
            class="px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-medium transition shrink-0">
            <i class="fas fa-plus"></i>
          </button>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Doctor <span class="text-red-500">*</span></label>
          <select v-model="form.doctor_id"
            class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
            <option value="">Select Doctor</option>
            <option v-for="d in doctors" :key="d.id" :value="d.id">Dr. {{ d.name }} - {{ d.specialization || 'N/A' }}</option>
          </select>
          <p v-if="form.errors.doctor_id" class="text-sm text-red-500 mt-1">{{ form.errors.doctor_id }}</p>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Consultation Fee</label>
          <input v-model="form.consultation_fee" type="text" readonly placeholder="Doctor consultation fee"
            class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 bg-gray-100 cursor-not-allowed focus:outline-none" />
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Symptoms / Chief Complaint</label>
          <textarea v-model="form.chief_complaint" rows="2" placeholder="Enter symptoms or chief complaint..."
            class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition resize-none"></textarea>
          <p v-if="form.errors.chief_complaint" class="text-sm text-red-500 mt-1">{{ form.errors.chief_complaint }}</p>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Notes</label>
          <textarea v-model="form.notes" rows="2" placeholder="Additional notes..."
            class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition resize-none"></textarea>
          <p v-if="form.errors.notes" class="text-sm text-red-500 mt-1">{{ form.errors.notes }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
          <button type="button" @click="$emit('close')"
            class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Cancel</button>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 transition">
            {{ form.processing ? 'Saving...' : 'Save Visit' }}
          </button>
        </div>
      </form>

      <CreatePatientModal v-if="showPatientModal" @close="showPatientModal = false" @created="addPatient" />
    </div>
  </div>
</template>
