<script setup>
import { reactive, ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import CreatePatientModal from '../Patients/CreateModal.vue'

const props = defineProps({
  patients: Array,
  doctors: Array,
})

const emit = defineEmits(['close'])

const showPatientModal = ref(false)

const localErrors = ref({})

const displayErrors = computed(() => {
  const pageErrors = usePage().props.errors || {}
  return { ...pageErrors, ...localErrors.value }
})

const form = reactive({
  patient_id: '',
  doctor_id: '',
  consultation_fee: '',
  chief_complaint: '',
  notes: '',
})

watch(
  () => form.doctor_id,
  (doctorId) => {
    const doctor = props.doctors.find(d => d.id == doctorId)
    form.consultation_fee = doctor?.consultation_fee || ''
  },
  { immediate: true }
)

const addPatient = (patient) => {
  props.patients.push(patient)
  form.patient_id = patient.id
  showPatientModal.value = false
}

const submit = () => {
  localErrors.value = {}
  router.post(route('visits.store'), form, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('close')
      router.reload({ only: ['visits'] })
    },
    onError: (err) => {
      localErrors.value = err
    },
  })
}
</script>
<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl p-6">

      <!-- Header -->
      <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">

        <h2 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
          <i class="fas fa-walk"></i>
          Create Visit
        </h2>

        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-2xl font-bold">
          &times;
        </button>

      </div>

      <p v-if="displayErrors.patient_id" class="text-red-500 text-sm">
        {{ displayErrors.patient_id }}
      </p>

      <p v-if="displayErrors.error" class="text-red-500 text-sm">
        {{ displayErrors.error }}
      </p>

      <!-- Form -->
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Patient -->
        <div class="md:col-span-2 flex flex-col gap-1">

          <div class="flex items-center justify-between">

            <label class="text-sm font-medium text-gray-700">
              Patient
            </label>

            <button type="button" @click="showPatientModal = true"
              class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-medium transition">
              <i class="fas fa-plus"></i> Patient
            </button>

          </div>

          <div class="flex gap-2">

            <select v-model="form.patient_id"
              class="w-full border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"
              :class="displayErrors.patient_id ? 'border-red-500 bg-red-50' : 'border-gray-300'">
              <option value="">Select Patient</option>

              <option v-for="p in patients" :key="p.id" :value="p.id">
                {{ p.name }} - {{ p.phone || 'N/A' }}
              </option>

            </select>

          </div>

          <!-- Error -->
          <p v-if="displayErrors.patient_id" class="text-sm text-red-500 mt-1">
            {{ displayErrors.patient_id }}
          </p>

        </div>

        <!-- Doctor -->
        <div class="flex flex-col gap-1">

          <label class="text-sm font-medium text-gray-700">
            Doctor
          </label>

          <select v-model="form.doctor_id"
            class="w-full border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"
            :class="displayErrors.doctor_id ? 'border-red-500 bg-red-50' : 'border-gray-300'">
            <option value="">Select Doctor</option>

            <option v-for="d in doctors" :key="d.id" :value="d.id">
              Dr. {{ d.name }} - {{ d.specialization || 'N/A' }}
            </option>

          </select>

          <!-- Error -->
          <p v-if="displayErrors.doctor_id" class="text-sm text-red-500 mt-1">
            {{ displayErrors.doctor_id }}
          </p>

        </div>

        <!-- CONSULTATION FEE -->
        <div class="flex flex-col gap-1">

          <label class="text-sm font-medium text-gray-700">
            Consultation Fee
          </label>

          <input v-model="form.consultation_fee" type="text" readonly placeholder="Doctor consultation fee"
            class="border border-gray-300 rounded-lg px-3 py-2.5 bg-gray-100 cursor-not-allowed focus:outline-none" />

        </div>

        <!-- Chief Complaint -->
        <div class="md:col-span-2 flex flex-col gap-1">

          <label class="text-sm font-medium text-gray-700">
            Symptoms / Chief Complaint
          </label>

          <textarea v-model="form.chief_complaint" rows="2" placeholder="Enter symptoms or chief complaint..."
            class="w-full border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition resize-none"
            :class="displayErrors.chief_complaint ? 'border-red-500 bg-red-50' : 'border-gray-300'"></textarea>

          <!-- Error -->
          <p v-if="displayErrors.chief_complaint" class="text-sm text-red-500 mt-1">
            {{ displayErrors.chief_complaint }}
          </p>

        </div>

        <!-- Notes -->
        <div class="md:col-span-2 flex flex-col gap-1">

          <label class="text-sm font-medium text-gray-700">
            Notes
          </label>

          <textarea v-model="form.notes" rows="2" placeholder="Additional notes..."
            class="w-full border rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition resize-none"
            :class="displayErrors.notes ? 'border-red-500 bg-red-50' : 'border-gray-300'"></textarea>

          <!-- Error -->
          <p v-if="displayErrors.notes" class="text-sm text-red-500 mt-1">
            {{ displayErrors.notes }}
          </p>

        </div>

        <!-- Actions -->
        <div class="md:col-span-2 flex justify-end gap-3 pt-3">

          <button type="button" @click="$emit('close')"
            class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
            Cancel
          </button>

          <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
            Save Visit
          </button>

        </div>

      </form>

      <!-- Patient Create Modal -->
      <CreatePatientModal v-if="showPatientModal" @close="showPatientModal = false" @created="addPatient" />

    </div>
  </div>
</template>
