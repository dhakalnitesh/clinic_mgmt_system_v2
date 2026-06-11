<script setup>
import { reactive, ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import CreateModal from '../Patients/CreateModal.vue'
import NepaliDatepicker from '@/Components/NepaliDatepicker.vue'

const props = defineProps({
  patients: Array,
  doctors: Array,
})

const emit = defineEmits(['close'])

const showPatientModal = ref(false)

/**
 * Backend validation errors
 */
const errors = usePage().props.errors

/**
 * Dynamic available doctors
 */
const availableDoctors = ref([...props.doctors])

const form = reactive({
  patient_id: '',
  doctor_id: '',
  consultation_fee: '',
  appointment_date: '',
  appointment_time: '',
  status: 'waiting',
})

/**
 * Fetch available doctors
 * when appointment date changes
 */
watch(
  () => form.appointment_date,
  async (date) => {

    if (!date) {
      availableDoctors.value = []
      form.doctor_id = ''
      form.consultation_fee = ''
      return
    }

    try {

      const response = await axios.get(
        route('appointments.index'),
        {
          params: {
            appointment_date: date
          }
        }
      )

      availableDoctors.value =
        response.data.doctors || []

      /**
       * Reset selected doctor
       * if not available anymore
       */
      const exists = availableDoctors.value.find(
        d => d.id == form.doctor_id
      )

      if (!exists) {
        form.doctor_id = ''
        form.consultation_fee = ''
      }

    } catch (error) {

      console.error(error)

      availableDoctors.value = []
    }
  }
)

/**
 * Auto update consultation fee
 */
watch(
  () => form.doctor_id,
  (doctorId) => {

    const doctor = availableDoctors.value.find(
      d => d.id == doctorId
    )

    form.consultation_fee =
      doctor?.consultation_fee || ''
  }
)

/**
 * Add newly created patient instantly
 */
const addPatient = (patient) => {

  props.patients.push(patient)

  form.patient_id = patient.id

  showPatientModal.value = false
}

/**
 * Submit appointment
 */
const submit = () => {

  router.post(route('appointments.store'), form, {
    preserveScroll: true,

    onSuccess: () => emit('close'),
  })
}
</script>
<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl p-6">

      <!-- Header -->
      <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">

        <h2 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
          <i class="fas fa-calendar-plus"></i>
          Create Appointment
        </h2>

        <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-2xl font-bold">
          &times;
        </button>

      </div>

      <p v-if="$page.props.errors.patient_id" class="text-red-500 text-sm">
        {{ $page.props.errors.patient_id }}
      </p>

      <p v-if="$page.props.errors.error" class="text-red-500 text-sm">
        {{ $page.props.errors.error }}
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
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
              <option value="">Select Patient</option>

              <option v-for="p in patients" :key="p.id" :value="p.id">
                {{ p.name }} - {{ p.phone || 'N/A' }}
              </option>

            </select>

          </div>

          <!-- Error -->
          <p v-if="errors.patient_id" class="text-sm text-red-500 mt-1">
            {{ errors.patient_id }}
          </p>

        </div>

        <!-- Date -->
        <div class="flex flex-col gap-1">

          <label class="text-sm font-medium text-gray-700">
            Date (BS)
          </label>

          <NepaliDatepicker v-model="form.appointment_date" placeholder="Select Nepali date" />

          <!-- Error -->
          <p v-if="errors.appointment_date" class="text-sm text-red-500 mt-1">
            {{ errors.appointment_date }}
          </p>

        </div>

        <!-- Time -->
        <div class="flex flex-col gap-1">

          <label class="text-sm font-medium text-gray-700">
            Time
          </label>

          <input v-model="form.appointment_time" type="time"
            class="border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />

          <!-- Error -->
          <p v-if="errors.appointment_time" class="text-sm text-red-500 mt-1">
            {{ errors.appointment_time }}
          </p>

        </div>

        <!-- Doctor -->
        <div class="flex flex-col gap-1">

          <label class="text-sm font-medium text-gray-700">
            Doctor
          </label>

          <select v-model="form.doctor_id"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
            <option value="">Select Doctor</option>

            <option v-for="d in availableDoctors" :key="d.id" :value="d.id">
              Dr. {{ d.name }} - {{ d.specialization || 'N/A' }}
            </option>

          </select>

          <!-- Error -->
          <p v-if="errors.doctor_id" class="text-sm text-red-500 mt-1">
            {{ errors.doctor_id }}
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



        <!-- Status -->
        <!-- <div class="md:col-span-2 flex flex-col gap-1"> <label class="text-sm font-medium text-gray-700"> Status </label> <select v-model="form.status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"> <option value="pending"> Pending </option> <option value="completed"> Completed </option> <option value="cancelled"> Cancelled </option> </select> </div> -->

        <!-- Actions -->
        <div class="md:col-span-2 flex justify-end gap-3 pt-3">

          <button type="button" @click="$emit('close')"
            class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
            Cancel
          </button>

          <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
            Save Appointment
          </button>

        </div>

      </form>

      <!-- Patient Create Modal -->
      <CreateModal v-if="showPatientModal" @close="showPatientModal = false" @created="addPatient" />

    </div>
  </div>
</template>