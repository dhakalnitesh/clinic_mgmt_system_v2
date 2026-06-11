<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')"></div>

    <div class="relative bg-white dark:bg-gray-800 w-full max-w-4xl rounded-xl shadow-2xl overflow-y-auto max-h-[90vh]">
      <!-- Header -->
      <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
        <div class="flex items-center gap-3">
          <div class="flex items-center justify-center size-12 rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
            <i class="fas fa-user text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ patient.name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Patient Details</p>
          </div>
        </div>
        <button @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none">&times;</button>
      </div>

      <div class="p-6 space-y-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Gender</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ patient.gender || '—' }}</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Age</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ patient.age || '—' }} years</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Phone</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ patient.phone || '—' }}</p>
          </div>
        </div>

        <!-- Address Info -->
        <div>
          <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-teal-600"></i> Address Information
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
              <p class="text-xs text-gray-500 dark:text-gray-400">Province</p>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ patient.province?.province_name || '—' }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
              <p class="text-xs text-gray-500 dark:text-gray-400">District</p>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ patient.district?.district_name || '—' }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
              <p class="text-xs text-gray-500 dark:text-gray-400">Municipality</p>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ patient.municipal?.municipal_name || '—' }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
              <p class="text-xs text-gray-500 dark:text-gray-400">Street / Tole</p>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ patient.address1 || '—' }}</p>
            </div>
          </div>
        </div>

        <!-- Citizenship Info -->
        <div v-if="patient.citizenship_type" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Citizenship Type</p>
              <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ patient.citizenship_type === 'nepali' ? 'Nepali Citizen' : 'Foreign Citizen' }}
              </p>
            </div>
            <div v-if="patient.certificate_number">
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Document Number</p>
              <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ patient.certificate_number }}</p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="patient.notes" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Notes</p>
          <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ patient.notes }}</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        <button @click="$emit('close')"
          class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  patient: { type: Object, required: true }
})

defineEmits(['close'])
</script>
