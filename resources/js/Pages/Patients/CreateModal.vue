<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-3xl shadow-2xl overflow-y-auto max-h-[90vh]">
      <!-- Header -->
      <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
          <i class="fas fa-user-plus text-teal-600 mr-2"></i>Create Patient
        </h3>
        <button @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none">&times;</button>
      </div>

      <form @submit.prevent="submit" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
              placeholder="Patient full name" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone <span class="text-red-500">*</span></label>
            <input v-model="form.phone" type="tel" maxlength="10"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
              placeholder="98XXXXXXXX" @input="form.phone = form.phone.replace(/[^0-9]/g, '')" />
            <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
          </div>

          <!-- Age -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Age</label>
            <input v-model="form.age" type="number" min="0" max="150"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
              placeholder="Age in years" />
            <p v-if="form.errors.age" class="text-red-500 text-xs mt-1">{{ form.errors.age }}</p>
          </div>

          <!-- Gender -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
            <select v-model="form.gender"
              class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
            <p v-if="form.errors.gender" class="text-red-500 text-xs mt-1">{{ form.errors.gender }}</p>
          </div>
        </div>

        <!-- Nepali Address Component -->
        <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
          <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Address Information</h4>
          <NepaliAddress
            :provinces="provinces"
            :districts="districts"
            :municipals="municipals"
            :form="form"
          />
        </div>

        <!-- Address 1 -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Street / Ward / Tole</label>
          <input v-model="form.address1" type="text"
            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
            placeholder="Street, ward number, or tole name" />
        </div>

        <!-- Notes -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
          <textarea v-model="form.notes" rows="2"
            class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
            placeholder="Any additional notes"></textarea>
        </div>

        <!-- Buttons -->
        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
          <button type="button" @click="$emit('close')"
            class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            Cancel
          </button>
          <button type="submit" :disabled="form.processing"
            class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-60 flex items-center gap-2">
            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-save"></i>
            {{ form.processing ? 'Saving...' : 'Save Patient' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import NepaliAddress from '@/Components/NepaliAddress.vue'

const props = defineProps({
  provinces: { type: Array, default: () => [] },
  districts: { type: Array, default: () => [] },
  municipals: { type: Array, default: () => [] },
})

const emit = defineEmits(['close', 'success'])

const form = useForm({
  name: '',
  age: '',
  gender: '',
  phone: '',
  address1: '',
  notes: '',
  citizenship_type: '',
  province_id: '',
  district_id: '',
  municipal_id: '',
  certificate_number: '',
  foreign_address: '',
})

const submit = () => {
  form.post(route('patients.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('success')
      emit('close')
    },
  })
}
</script>
