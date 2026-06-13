<template>
  <AuthenticatedLayout>
    <Head title="Book Appointment" />

    <div class="max-w-2xl mx-auto">
      <!-- Already created: show success message -->
      <div v-if="alreadyCreated" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
        <div class="text-5xl mb-4 text-indigo-500">
          <i class="fas fa-check-circle"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Appointment Booked!</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          Your appointment has been booked successfully.
        </p>
        <Link href="/dashboard"
          class="inline-block px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition">
          <i class="fas fa-home mr-2"></i>Go to Dashboard
        </Link>
      </div>

      <!-- Create Form -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-bold text-indigo-600 flex items-center gap-2">
            <i class="fas fa-calendar-plus"></i>
            Book Appointment
          </h2>
        </div>

        <p v-if="$page.props.errors.patient_id" class="text-red-500 text-sm px-6 pt-4">
          {{ $page.props.errors.patient_id }}
        </p>
        <p v-if="$page.props.errors.error" class="text-red-500 text-sm px-6 pt-4">
          {{ $page.props.errors.error }}
        </p>

        <form @submit.prevent="submit" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

          <div class="md:col-span-2 flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Patient</label>
            <div class="flex items-center gap-3 px-3 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
              <i class="fas fa-user text-gray-400"></i>
              <div>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ patient.name }}</p>
                <p class="text-xs text-gray-500">Phone: {{ patient.phone || 'N/A' }}</p>
              </div>
            </div>
            <p v-if="$page.props.errors.patient_id" class="text-sm text-red-500 mt-1">
              {{ $page.props.errors.patient_id }}
            </p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date (BS)</label>
            <NepaliDatepicker v-model="form.appointment_date" placeholder="Select Nepali date" :min-date="minDate" :max-date="maxDate" />
            <p v-if="$page.props.errors.appointment_date" class="text-sm text-red-500 mt-1">
              {{ $page.props.errors.appointment_date }}
            </p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Time</label>
            <input v-model="form.appointment_time" type="time"
              class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
            <p v-if="$page.props.errors.appointment_time" class="text-sm text-red-500 mt-1">
              {{ $page.props.errors.appointment_time }}
            </p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Doctor</label>
            <select v-model="form.doctor_id"
              class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition">
              <option value="">Select Doctor</option>
              <option v-for="d in availableDoctors" :key="d.id" :value="d.id">
                Dr. {{ d.name }} - {{ d.specialization || 'N/A' }}
              </option>
            </select>
            <p v-if="$page.props.errors.doctor_id" class="text-sm text-red-500 mt-1">
              {{ $page.props.errors.doctor_id }}
            </p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Fee</label>
            <input v-model="form.consultation_fee" type="text" readonly placeholder="Doctor consultation fee"
              class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2.5 bg-gray-100 dark:bg-gray-600 cursor-not-allowed focus:outline-none" />
          </div>

          <div class="md:col-span-2 flex justify-end gap-3 pt-3 border-t border-gray-200 dark:border-gray-700 mt-2">
            <Link href="/dashboard"
              class="px-5 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
              Cancel
            </Link>
            <button type="submit"
              class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center gap-2">
              <i class="fas fa-save"></i> Save Appointment
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import NepaliDatepicker from '@/Components/NepaliDatepicker.vue'

const props = defineProps({
  patient: Object,
  doctors: Array,
  alreadyCreated: { type: Boolean, default: false },
  minDate: { type: String, default: '' },
  maxDate: { type: String, default: '' },
})

const availableDoctors = ref([...props.doctors])
const errors = usePage().props.errors

const form = reactive({
  patient_id: props.patient?.id || '',
  doctor_id: '',
  consultation_fee: '',
  appointment_date: '',
  appointment_time: '',
  status: 'waiting',
})

watch(() => form.appointment_date, async (date) => {
  if (!date) {
    availableDoctors.value = [...(props.doctors || [])]
    form.doctor_id = ''
    form.consultation_fee = ''
    return
  }
  try {
    const response = await axios.get(route('guest.doctors.available'), {
      params: { appointment_date: date }
    })
    availableDoctors.value = response.data.doctors || []
    const exists = availableDoctors.value.find(d => d.id == form.doctor_id)
    if (!exists) {
      form.doctor_id = ''
      form.consultation_fee = ''
    }
  } catch (error) {
    console.error(error)
  }
})

watch(() => form.doctor_id, (doctorId) => {
  const doctor = availableDoctors.value.find(d => d.id == doctorId)
  form.consultation_fee = doctor?.consultation_fee || ''
})

const submit = () => {
  router.post(route('guest.appointments.store'), form, {
    preserveScroll: true,
    onSuccess: () => {
      form.patient_id = ''
      form.doctor_id = ''
      form.consultation_fee = ''
      form.appointment_date = ''
      form.appointment_time = ''
    },
  })
}
</script>
