<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl p-5">

      <!-- HEADER -->
      <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-5">

        <div>
          <h2 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
            <i class="fas fa-calendar-edit"></i>
            Edit Appointment
          </h2>

          <p class="text-xs text-gray-500 mt-1">
            Update appointment details
          </p>
        </div>

        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-2xl font-bold">
          &times;
        </button>

      </div>

      <!-- FORM -->
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- ================= PATIENT ================= -->
        <div class="md:col-span-2 relative" ref="patientDropdownRef">

          <div class="flex items-center justify-between mb-1">

            <label class="text-sm font-semibold text-gray-700">
              Patient
            </label>

            <button type="button" @click="showPatientModal = true"
              class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-medium transition">
              <i class="fas fa-plus"></i> Patient
            </button>

          </div>

          <div class="relative">

            <input v-model="patientSearch" @focus="showPatientDropdown = true" @input="showPatientDropdown = true"
              type="text" placeholder="Search patient..." :class="[
                'w-full border rounded-xl px-4 py-2.5 pr-10 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none',
                form.errors.patient_id ? 'border-red-400 bg-red-50' : 'border-gray-300'
              ]" />

            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

          </div>

          <!-- VALIDATION ERROR -->
          <p v-if="form.errors.patient_id" class="mt-1 text-xs text-red-500 flex items-center gap-1">
            <i class="fas fa-circle-exclamation"></i>
            {{ form.errors.patient_id }}
          </p>

          <!-- DROPDOWN -->
          <div v-if="showPatientDropdown && filteredPatients.length > 0"
            class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">

            <div class="max-h-56 overflow-y-auto">

              <div v-for="patient in filteredPatients" :key="patient.id" @mousedown.prevent="selectPatient(patient)"
                class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100">

                <div class="flex justify-between">

                  <div class="font-medium text-gray-800">
                    {{ patient.name }}
                  </div>

                  <div v-if="Number(form.patient_id) === Number(patient.id)"
                    class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
                    Selected
                  </div>

                </div>

                <div class="text-xs text-gray-500 mt-1">
                  {{ patient.phone || '-' }}
                </div>

              </div>

              <div v-if="filteredPatients.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                No patient found
              </div>

            </div>

          </div>

        </div>

        <!-- ================= DOCTOR ================= -->
        <div class="relative" ref="doctorDropdownRef">

          <label class="text-sm font-semibold text-gray-700 mb-1 block">
            Doctor
          </label>

          <div class="relative">

            <input v-model="doctorSearch" @focus="showDoctorDropdown = true" @input="showDoctorDropdown = true"
              type="text" placeholder="Search doctor..." :class="[
                'w-full border rounded-xl px-4 py-2.5 pr-10 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none',
                form.errors.doctor_id ? 'border-red-400 bg-red-50' : 'border-gray-300'
              ]" />

            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

          </div>

          <!-- VALIDATION ERROR -->
          <p v-if="form.errors.doctor_id" class="mt-1 text-xs text-red-500 flex items-center gap-1">
            <i class="fas fa-circle-exclamation"></i>
            {{ form.errors.doctor_id }}
          </p>

          <!-- DROPDOWN -->
          <div v-if="showDoctorDropdown && filteredDoctors.length > 0"
            class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">

            <div class="max-h-56 overflow-y-auto">

              <div v-for="doctor in filteredDoctors" :key="doctor.id" @mousedown.prevent="selectDoctor(doctor)"
                class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100">

                <div class="flex justify-between">

                  <div class="font-medium text-gray-800">
                    Dr. {{ doctor.name }}
                  </div>

                  <div v-if="Number(form.doctor_id) === Number(doctor.id)"
                    class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
                    Selected
                  </div>

                </div>

                <div class="text-xs text-gray-500 mt-1">
                  {{ doctor.specialization || '-' }}
                </div>

              </div>

              <div v-if="filteredDoctors.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                No doctor found
              </div>

            </div>

          </div>

        </div>

        <!-- DATE -->
        <div>

          <label class="text-sm font-medium text-gray-700 mb-1 block">
            Date
          </label>

          <input v-model="form.appointment_date" type="date" :class="[
            'w-full border rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none',
            form.errors.appointment_date ? 'border-red-400 bg-red-50' : 'border-gray-300'
          ]" />

          <!-- VALIDATION ERROR -->
          <p v-if="form.errors.appointment_date" class="mt-1 text-xs text-red-500 flex items-center gap-1">
            <i class="fas fa-circle-exclamation"></i>
            {{ form.errors.appointment_date }}
          </p>

        </div>

        <!-- TIME -->
        <div>

          <label class="text-sm font-medium text-gray-700 mb-1 block">
            Time
          </label>

          <input v-model="form.appointment_time" type="time" :class="[
            'w-full border rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none',
            form.errors.appointment_time ? 'border-red-400 bg-red-50' : 'border-gray-300'
          ]" />

          <!-- VALIDATION ERROR -->
          <p v-if="form.errors.appointment_time" class="mt-1 text-xs text-red-500 flex items-center gap-1">
            <i class="fas fa-circle-exclamation"></i>
            {{ form.errors.appointment_time }}
          </p>

        </div>

        <!-- STATUS -->
        <div class="md:col-span-2">

          <label class="text-sm font-medium text-gray-700 mb-1 block">
            Status
          </label>

          <select v-model="form.status" :class="[
            'w-full border rounded-xl px-4 py-2.5',
            form.errors.status ? 'border-red-400 bg-red-50' : 'border-gray-300'
          ]">
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>

          <!-- VALIDATION ERROR -->
          <p v-if="form.errors.status" class="mt-1 text-xs text-red-500 flex items-center gap-1">
            <i class="fas fa-circle-exclamation"></i>
            {{ form.errors.status }}
          </p>

        </div>

        <!-- ACTIONS -->
        <div class="md:col-span-2 flex justify-end gap-3 pt-2">

          <button type="button" @click="$emit('close')"
            class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
            Cancel
          </button>

          <button type="submit" :disabled="form.processing"
            class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60">
            {{ form.processing ? 'Updating...' : 'Update Appointment' }}
          </button>

        </div>

      </form>

      <!-- CREATE PATIENT MODAL -->
      <CreateModal v-if="showPatientModal" @close="showPatientModal = false" @created="addPatient" />

    </div>
  </div>
</template>

<script setup>
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
} from 'vue'

import { useForm } from '@inertiajs/vue3'
import CreateModal from '../Patients/CreateModal.vue'

// ---------------------------------------------------------------------------
// EMITS & PROPS
// ---------------------------------------------------------------------------

const emit = defineEmits(['close'])

const props = defineProps({
  appointment: {
    type: Object,
    default: () => ({}),
  },
  patients: {
    type: Array,
    default: () => [],
  },
  doctors: {
    type: Array,
    default: () => [],
  },
})

// ---------------------------------------------------------------------------
// UI STATE
// ---------------------------------------------------------------------------

const showPatientModal = ref(false)
const showPatientDropdown = ref(false)
const showDoctorDropdown = ref(false)
const patientDropdownRef = ref(null)
const doctorDropdownRef = ref(null)

const patientSearch = ref('')
const doctorSearch = ref('')

// ---------------------------------------------------------------------------
// LOCAL PATIENTS LIST
// ---------------------------------------------------------------------------

const localPatients = ref([...props.patients])

// ---------------------------------------------------------------------------
// FORM
// ---------------------------------------------------------------------------

const form = useForm({
  id: props.appointment?.id ?? '',
  patient_id: props.appointment?.patient_id
    ? Number(props.appointment.patient_id)
    : '',
  doctor_id: props.appointment?.doctor_id
    ? Number(props.appointment.doctor_id)
    : '',
  appointment_date: props.appointment?.appointment_date ?? '',
  appointment_time: props.appointment?.appointment_time ?? '',
  status: props.appointment?.status ?? 'pending',
})

// ---------------------------------------------------------------------------
// FILTERED LISTS
// ---------------------------------------------------------------------------

const filteredPatients = computed(() => {
  const q = patientSearch.value.toLowerCase().trim()
  if (!q) return localPatients.value
  return localPatients.value.filter(p =>
    p.name?.toLowerCase().includes(q) ||
    p.phone?.toLowerCase().includes(q),
  )
})

const filteredDoctors = computed(() => {
  const q = doctorSearch.value.toLowerCase().trim()
  if (!q) return props.doctors
  return props.doctors.filter(d =>
    d.name?.toLowerCase().includes(q) ||
    d.specialization?.toLowerCase().includes(q),
  )
})

// ---------------------------------------------------------------------------
// SELECT HELPERS
// ---------------------------------------------------------------------------

const selectPatient = (patient) => {
  form.patient_id = Number(patient.id)
  patientSearch.value = `${patient.name}`
  showPatientDropdown.value = false
}

const selectDoctor = (doctor) => {
  form.doctor_id = Number(doctor.id)
  doctorSearch.value = `${doctor.name}`
  showDoctorDropdown.value = false
}

// ---------------------------------------------------------------------------
// CLICK-OUTSIDE HANDLER
// ---------------------------------------------------------------------------

const handleClickOutside = (event) => {
  if (
    patientDropdownRef.value &&
    !patientDropdownRef.value.contains(event.target)
  ) {
    showPatientDropdown.value = false
  }

  if (
    doctorDropdownRef.value &&
    !doctorDropdownRef.value.contains(event.target)
  ) {
    showDoctorDropdown.value = false
  }
}

// ---------------------------------------------------------------------------
// LIFECYCLE
// ---------------------------------------------------------------------------

onMounted(() => {
  window.addEventListener('click', handleClickOutside)

  const currentPatient = localPatients.value.find(
    (p) => Number(p.id) === Number(form.patient_id),
  )
  patientSearch.value = currentPatient ? `${currentPatient.name}` : ''

  const currentDoctor = props.doctors.find(
    (d) => Number(d.id) === Number(form.doctor_id),
  )
  doctorSearch.value = currentDoctor ? `${currentDoctor.name}` : ''
})

onBeforeUnmount(() => {
  window.removeEventListener('click', handleClickOutside)
})

// ---------------------------------------------------------------------------
// ADD PATIENT
// ---------------------------------------------------------------------------

const addPatient = (patient) => {
  localPatients.value.push(patient)
  selectPatient(patient)
  showPatientModal.value = false
}

// ---------------------------------------------------------------------------
// SUBMIT
// ---------------------------------------------------------------------------

const submit = () => {
  form.put(
    route('appointments.update', { appointment: form.id }),
    {
      preserveScroll: true,
      onSuccess: () => emit('close'),
    },
  )
}
</script>