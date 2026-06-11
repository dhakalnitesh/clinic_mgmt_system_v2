<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-3xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">

      <!-- Header -->
      <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-3">
        <div>
          <h3 class="text-2xl font-bold text-indigo-600">Edit Visit</h3>
          <p class="text-sm text-gray-500 mt-1">
            Update patient visit and consultation entry
          </p>
        </div>

        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">
          &times;
        </button>
      </div>

      <form @submit.prevent="submit">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- PATIENT -->
          <div class="flex flex-col md:col-span-2">
            <label class="text-sm font-semibold text-gray-700">Patient</label>

            <input type="text" disabled
              class="mt-2 border border-gray-300 rounded-xl px-4 py-3 bg-gray-100 text-gray-600"
              :value="patientLabel" />
          </div>

          <!-- DOCTOR -->
          <div class="flex flex-col relative" ref="doctorDropdownRef">

            <label class="text-sm font-semibold text-gray-700">
              Doctor
            </label>

            <div class="relative mt-2">

              <input v-model="doctorSearch" @focus="showDoctorDropdown = true" type="text"
                placeholder="Search doctor..."
                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-10 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" />

              <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>

            </div>

            <!-- Dropdown -->
            <div v-if="showDoctorDropdown"
              class="absolute top-full z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden">
              <div class="max-h-72 overflow-y-auto">

                <div v-for="doctor in filteredDoctors" :key="doctor.id" @click="selectDoctor(doctor)"
                  class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100">
                  <div class="flex justify-between">
                    <div class="font-semibold">
                      Dr. {{ doctor.name }}
                    </div>

                    <div v-if="form.doctor_id === doctor.id"
                      class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
                      Selected
                    </div>
                  </div>

                  <div class="text-sm text-gray-500">
                    {{ doctor.specialization || '-' }}
                  </div>

                </div>

                <div v-if="filteredDoctors.length === 0" class="px-4 py-6 text-center text-gray-500 text-sm">
                  No doctor found
                </div>

              </div>
            </div>

          </div>

          <!-- APPOINTMENT -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Appointment</label>
            <input type="text" disabled
              class="mt-2 border border-gray-300 rounded-xl px-4 py-3 bg-gray-100 text-gray-600"
              :value="props.appointments?.appointment_date || visit.visited_at" />

            <!-- <select
              v-model="form.appointment_id"
              class="mt-2 border border-gray-300 rounded-xl px-4 py-3"
            >
              <option value="">-- Select Appointment --</option>

              <option
                v-for="a in appointments"
                :key="a.id"
                :value="a.id" 
              >
                {{ a.appointment_date }} - {{ a.appointment_time }} ?? {{props.visit.visited_at}}
              </option>

            </select> -->
          </div>

          <!-- STATUS (NEW) -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Status</label>

            <select v-model="form.status"
              class="mt-2 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 focus:outline-none transition">
              <option value="" disabled>All Status</option>
              <option value="waiting">Waiting</option>
              <option value="in_consultation">In Consultation</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- SYMPTOMS -->
          <div class="md:col-span-2 flex flex-col">
            <label class="text-sm font-medium text-gray-700">Symptoms</label>

            <textarea v-model="form.symptoms" readonly rows="2" class="mt-2  border border-gray-300 rounded-xl px-4 py-3 cursor-not-allowed " />
          </div>

          <!-- DIAGNOSIS -->
          <div class="md:col-span-2 flex flex-col">
            <label class="text-sm font-medium text-gray-700">Diagnosis</label>

            <textarea v-model="form.diagnosis"  rows="2"  class="mt-2  border border-gray-300 rounded-xl px-4 py-3" />
          </div>

          <!-- NOTES -->
          <div class="md:col-span-2 flex flex-col">
            <label class="text-sm font-medium text-gray-700">Notes</label>

            <textarea v-model="form.notes" rows="2" class="mt-2 border border-gray-300 rounded-xl px-4 py-3" />
          </div>

        </div>

        <!-- BUTTONS -->
        <div class="mt-8 flex justify-end gap-4 border-t pt-6">

          <button type="button" @click="$emit('close')"
            class="px-6 py-2.5 rounded-xl border text-gray-700 hover:bg-gray-100">
            Cancel
          </button>

          <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
            Update Visit
          </button>

        </div>

      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const emit = defineEmits(['close'])

const props = defineProps({
  visit: Object,
  doctors: Array,
  appointments: Array
})

/**
 * UI STATE
 */
const doctorSearch = ref('')
const showDoctorDropdown = ref(false)
const doctorDropdownRef = ref(null)

/**
 * FORM
 */
const form = useForm({
  id: null,
  patient_id: null,
  doctor_id: null,
  appointment_id: null,
  status: '', // ✅ NEW FIELD
  symptoms: '',
  diagnosis: '',
  notes: ''
})

/**
 * PATIENT LABEL
 */
const patientLabel = computed(() => {
  return props.visit?.patient
    ? `${props.visit.patient.name} - ${props.visit.patient.phone || ''}`
    : 'N/A'
})

/**
 * FILTER DOCTORS
 */
const filteredDoctors = computed(() => {
  const doctors = Array.isArray(props.doctors) ? props.doctors : []

  if (!doctorSearch.value) return doctors

  const s = doctorSearch.value.toLowerCase().trim()

  return doctors.filter(d =>
    (d.name || '').toLowerCase().includes(s) ||
    (d.specialization || '').toLowerCase().includes(s) ||
    (d.phone || '').toLowerCase().includes(s) ||
    (d.nmc_number || '').toLowerCase().includes(s)
  )
})

/**
 * SELECT DOCTOR
 */
const selectDoctor = (doctor) => {
  form.doctor_id = doctor.id
  doctorSearch.value = doctor.name
  showDoctorDropdown.value = false
}

/**
 * SYNC DATA
 */
watch(
  () => props.visit,
  (v) => {
    if (!v) return

    form.id = v.id
    form.patient_id = v.patient_id
    form.doctor_id = v.doctor_id
    form.appointment_id = v.appointment_id
    form.status = v.status || ''   // ✅ IMPORTANT FIX
    form.symptoms = v.symptoms
    form.diagnosis = v.diagnosis
    form.notes = v.notes

    const doc = props.doctors?.find(d => d.id === v.doctor_id)
    doctorSearch.value = doc ? doc.name : ''
  },
  { immediate: true }
)

/**
 * OUTSIDE CLICK
 */
const handleClickOutside = (e) => {
  if (doctorDropdownRef.value && !doctorDropdownRef.value.contains(e.target)) {
    showDoctorDropdown.value = false
  }
}

window.addEventListener('click', handleClickOutside)

/**
 * UPDATE FUNCTION (FIXED + CLEAN)
 */
const submit = () => {
  form.put(route('visits.update', form.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('close')
    }
  })
}
</script>