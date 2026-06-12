<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const emit = defineEmits(['close'])

const props = defineProps({
  doctor: Object
})

const weekdays = [
  'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'
]

const everydayAvailability = ref(false)

const normalizeSchedule = (schedules) => {
  if (!schedules || !Array.isArray(schedules)) return []
  return schedules.map(item => ({
    day: item.day ?? '',
    start_time: item.start_time ?? null,
    end_time: item.end_time ?? null,
  }))
}

const form = useForm({
  _method: 'put',
  name: props.doctor?.name ?? '',
  nmc_number: props.doctor?.nmc_number ?? '',
  phone: props.doctor?.phone ?? '',
  specialization: props.doctor?.specialization ?? '',
  consultation_fee: props.doctor?.consultation_fee ?? '',
  photo: null,
  address1: props.doctor?.address1 ?? '',
  notes: props.doctor?.notes ?? '',
  doctor_schedule: normalizeSchedule(props.doctor?.schedules)
})

const photoPreview = ref(null)

const currentPhotoUrl = computed(() => {
  if (photoPreview.value) return photoPreview.value
  return props.doctor?.photo_url ?? null
})

const clearFieldError = (field) => {
  form.clearErrors(field)
}

const getTodayDay = () => weekdays[new Date().getDay()]

const addSchedule = () => {
  if (!Array.isArray(form.doctor_schedule)) {
    form.doctor_schedule = []
  }
  form.doctor_schedule.push({
    day: getTodayDay(),
    start_time: null,
    end_time: null,
  })
}

const removeSchedule = (index) => {
  form.doctor_schedule.splice(index, 1)
}

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

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.photo = file
    form.clearErrors('photo')
    const reader = new FileReader()
    reader.onload = (e) => { photoPreview.value = e.target.result }
    reader.readAsDataURL(file)
  }
}

const submit = () => {
  form.clearErrors()
  form.post(route('doctors.update', props.doctor.id), {
    preserveScroll: true,
    onSuccess: () => emit('close')
  })
}
</script>

<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-3xl shadow-2xl overflow-y-auto max-h-[90vh]">
      <!-- HEADER -->
      <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
          <i class="fas fa-user-edit text-teal-600 mr-2"></i>Edit Doctor
        </h3>
        <button @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none">&times;</button>
      </div>

      <form @submit.prevent="submit" enctype="multipart/form-data" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <!-- NAME -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
              @input="clearFieldError('name')" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- NMC -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NMC Number <span class="text-red-500">*</span></label>
            <input v-model="form.nmc_number" type="text"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
              @input="clearFieldError('nmc_number')" />
            <p v-if="form.errors.nmc_number" class="text-red-500 text-xs mt-1">{{ form.errors.nmc_number }}</p>
          </div>

          <!-- PHONE -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone <span class="text-red-500">*</span></label>
            <input v-model="form.phone" type="text" maxlength="10"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
              @input="clearFieldError('phone')" />
            <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
          </div>

          <!-- SPECIALIZATION -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
            <input v-model="form.specialization" type="text"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500" />
          </div>

          <!-- FEE -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Fee (Rs.)</label>
            <input v-model="form.consultation_fee" type="number"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500" />
          </div>

          <!-- ADDRESS -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
            <input v-model="form.address1" type="text"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500" />
          </div>

          <!-- PHOTO -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Photo</label>
            <div v-if="currentPhotoUrl" class="mt-2">
              <img :src="currentPhotoUrl" alt="Doctor photo" class="h-20 w-20 rounded-lg object-cover border border-gray-200 dark:border-gray-600" />
              <p class="text-xs text-gray-400 mt-1">{{ photoPreview ? 'New photo' : 'Current photo' }}</p>
            </div>
            <input type="file" accept="image/*" @change="handlePhotoUpload"
              class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 dark:file:bg-teal-900/30 dark:file:text-teal-300" />
            <p v-if="form.errors.photo" class="text-red-500 text-xs mt-1">{{ form.errors.photo }}</p>
          </div>

          <!-- SCHEDULE -->
          <div class="md:col-span-2">
            <div class="flex items-center justify-between gap-3 flex-wrap mb-3">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Availability Schedule</label>
              <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                  <input type="checkbox" v-model="everydayAvailability" @change="handleEverydayAvailability"
                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" />
                  Available Everyday
                </label>
                <button type="button" @click="addSchedule"
                  class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-700 bg-teal-50 dark:bg-teal-900/30 px-3 py-1.5 rounded-lg transition-colors">
                  + Add Day
                </button>
              </div>
            </div>

            <div v-if="form.doctor_schedule.length" class="space-y-2">
              <div v-for="(schedule, index) in form.doctor_schedule" :key="index"
                class="grid grid-cols-1 sm:grid-cols-4 gap-2 items-end border border-gray-200 dark:border-gray-700 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                <div>
                  <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Day</label>
                  <select v-model="schedule.day"
                    class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-2 py-2 text-sm">
                    <option v-for="day in weekdays" :key="day" :value="day">{{ day }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Start Time</label>
                  <input type="time" v-model="schedule.start_time"
                    class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-2 py-2 text-sm" />
                </div>
                <div>
                  <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">End Time</label>
                  <input type="time" v-model="schedule.end_time"
                    class="block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-2 py-2 text-sm" />
                </div>
                <div>
                  <button type="button" @click="removeSchedule(index)"
                    class="w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors border border-red-200 dark:border-red-900/30">
                    <i class="fas fa-trash-alt mr-1"></i>Remove
                  </button>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">No schedule added. Click "+ Add Day" to add availability.</p>
          </div>

          <!-- NOTES -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
            <textarea v-model="form.notes" rows="3"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
          </div>

        </div>

        <!-- BUTTONS -->
        <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
          <button type="button" @click="$emit('close')"
            class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            Cancel
          </button>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-60 flex items-center gap-2">
            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-save"></i>
            {{ form.processing ? 'Updating...' : 'Update Doctor' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
