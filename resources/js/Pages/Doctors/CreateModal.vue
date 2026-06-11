<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const emit = defineEmits(['close'])

/**
 * WEEKDAYS
 */
const weekdays = [
  'Sunday',
  'Monday',
  'Tuesday',
  'Wednesday',
  'Thursday',
  'Friday',
  'Saturday'
]

/**
 * EVERYDAY CHECKBOX
 */
const everydayAvailability = ref(false)

/**
 * FORM (backend handles validation)
 */
const form = useForm({
  name: '',
  nmc_number: '',
  phone: '',
  specialization: '',
  consultation_fee: '',
  photo: null,
  address1: '',
  notes: '',

  doctor_schedule: []
})

/**
 * CLEAR FIELD ERROR (backend errors only)
 */
const clearFieldError = (field) => {
  form.clearErrors(field)
}

/**
 * GET TODAY DAY
 */
const getTodayDay = () => {
  return weekdays[new Date().getDay()]
}

/**
 * ADD SCHEDULE ROW
 */
const addSchedule = () => {
  form.doctor_schedule.push({
    day: getTodayDay(),
    start_time: null,
    end_time: null,
  })
}

/**
 * REMOVE SCHEDULE ROW
 */
const removeSchedule = (index) => {
  form.doctor_schedule.splice(index, 1)
}

/**
 * EVERYDAY TOGGLE
 */
const handleEverydayAvailability = () => {

  if (everydayAvailability.value) {

    form.doctor_schedule = weekdays.map(day => ({
      day,
      start_time: null,
      end_time: null,
    }))

  } else {

    form.doctor_schedule = []
  }
}

/**
 * PHOTO UPLOAD
 */
const handlePhotoUpload = (event) => {

  const file = event.target.files[0]

  if (file) {
    form.photo = file
    form.clearErrors('photo')
  }
}

/**
 * SUBMIT (ONLY BACKEND VALIDATION)
 */
const submit = () => {

  form.clearErrors()

  form.post(route('doctors.store'), {

    preserveScroll: true,
    forceFormData: true,

    onSuccess: () => {

      form.reset()

      form.doctor_schedule = []

      everydayAvailability.value = false

      emit('close')
    }
  })
}
</script>

<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl w-full max-w-3xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">

      <!-- HEADER -->
      <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-3">

        <h3 class="text-2xl font-bold text-indigo-600">
          Create Doctor
        </h3>

        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">
          &times;
        </button>

      </div>

      <form @submit.prevent="submit" enctype="multipart/form-data">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- NAME -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Name <span class="text-red-600">*</span></label>

            <input v-model="form.name" type="text" class="mt-2 border rounded-lg px-3 py-2"
              @input="clearFieldError('name')" />

            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
              {{ form.errors.name }}
            </div>
          </div>

          <!-- NMC -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">NMC Number <span class="text-red-600">*</span></label>

            <input v-model="form.nmc_number" type="text" class="mt-2 border rounded-lg px-3 py-2"
              @input="clearFieldError('nmc_number')" />

            <div v-if="form.errors.nmc_number" class="text-red-600 text-sm mt-1">
              {{ form.errors.nmc_number }}
            </div>
          </div>

          <!-- PHONE -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Phone <span class="text-red-600">*</span></label>

            <input v-model="form.phone" type="text" class="mt-2 border rounded-lg px-3 py-2"
              @input="clearFieldError('phone')" />

            <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">
              {{ form.errors.phone }}
            </div>
          </div>

          <!-- SPECIALIZATION -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Specialization</label>

            <input v-model="form.specialization" type="text" class="mt-2 border rounded-lg px-3 py-2" />
          </div>

          <!-- FEE -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Consultation Fee</label>

            <input v-model="form.consultation_fee" type="number" class="mt-2 border rounded-lg px-3 py-2" />
          </div>

          <!-- SCHEDULE -->
          <div class="flex flex-col md:col-span-2">

            <div class="flex items-center justify-between gap-3 flex-wrap">

              <label class="text-sm font-medium text-gray-700">
                Availability Schedule
              </label>

              <div class="flex items-center gap-2">
                <input type="checkbox" v-model="everydayAvailability" @change="handleEverydayAvailability" />
                <span class="text-sm">Available Everyday</span>
              </div>

              <button type="button" @click="addSchedule" class="text-sm bg-indigo-100 px-3 py-1 rounded-lg">
                + Add Day
              </button>

            </div>

            <div class="space-y-3 mt-3">

              <div v-for="(schedule, index) in form.doctor_schedule" :key="index"
                class="grid grid-cols-1 md:grid-cols-4 gap-3 border p-4 rounded-xl">

                <!-- DAY -->
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-700">
                    Select Day
                  </label>

                  <select v-model="schedule.day" class="border rounded-lg px-3 py-2">
                    <option v-for="day in weekdays" :key="day" :value="day">
                      {{ day }}
                    </option>
                  </select>
                </div>

                <!-- START TIME -->
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-700">
                    Start Time
                  </label>

                  <input type="time" v-model="schedule.start_time" class="border rounded-lg px-3 py-2" />
                </div>

                <!-- END TIME -->
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-700">
                    End Time
                  </label>

                  <input type="time" v-model="schedule.end_time" class="border rounded-lg px-3 py-2" />
                </div>

                <!-- REMOVE -->
                <div class="flex items-end">
                  <button type="button" @click="removeSchedule(index)" class="text-red-600 hover:text-red-800">
                    Remove
                  </button>
                </div>

              </div>

            </div>

          </div>

          <!-- ADDRESS -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Address</label>

            <input v-model="form.address1" type="text" class="mt-2 border rounded-lg px-3 py-2" />
          </div>

          <!-- PHOTO -->
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700">Photo</label>

            <input type="file" accept="image/*" @change="handlePhotoUpload" class="mt-2" />

            <div v-if="form.errors.photo" class="text-red-600 text-sm mt-1">
              {{ form.errors.photo }}
            </div>
          </div>

          <!-- NOTES -->
          <div class="flex flex-col md:col-span-2">
            <label class="text-sm font-medium text-gray-700">Notes</label>

            <textarea v-model="form.notes" rows="4" class="mt-2 border rounded-lg px-3 py-2"></textarea>
          </div>

        </div>

        <!-- BUTTONS -->
        <div class="mt-8 flex justify-end gap-4">

          <button type="button" @click="$emit('close')" class="px-6 py-2 border rounded-lg">
            Cancel
          </button>

          <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-lg">
            {{ form.processing ? 'Saving...' : 'Save' }}
          </button>

        </div>

      </form>

    </div>
  </div>
</template>