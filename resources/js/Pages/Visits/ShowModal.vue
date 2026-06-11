<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-3xl p-8 shadow-2xl overflow-y-auto max-h-[90vh]">

      <!-- Header -->
      <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-3">
        <div>
          <h3 class="text-2xl font-bold text-indigo-600">
            Visit Details
          </h3>
          <p class="text-sm text-gray-500 mt-1">
            Full consultation information
          </p>
        </div>

        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-700 text-3xl font-bold"
        >
          &times;
        </button>
      </div>

      <!-- BODY -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- PATIENT -->
        <div class="flex flex-col md:col-span-2">
          <label class="text-sm font-semibold text-gray-700">
            Patient
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800">
            {{ visit?.patient?.name || 'N/A' }}
            <span class="text-gray-500">
              ({{ visit?.patient?.phone || '-' }})
            </span>
          </div>
        </div>

        <!-- DOCTOR -->
        <div class="flex flex-col">
          <label class="text-sm font-semibold text-gray-700">
            Doctor
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800">
            Dr. {{ visit?.doctor?.name || 'N/A' }}
            <div class="text-sm text-gray-500 mt-1">
              {{ visit?.doctor?.specialization || '-' }}
            </div>
          </div>
        </div>

        <!-- APPOINTMENT -->
        <div class="flex flex-col">
          <label class="text-sm font-medium text-gray-700">
            Appointment
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800">
            {{ visit?.appointment?.appointment_date || '-' }}
            -
            {{ visit?.appointment?.appointment_time || '-' }}
          </div>
        </div>

        <!-- SYMPTOMS -->
        <div class="flex flex-col md:col-span-2">
          <label class="text-sm font-medium text-gray-700">
            Symptoms / Chief Complaint
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 whitespace-pre-wrap">
            {{ visit?.symptoms || '-' }}
          </div>
        </div>

        <!-- DIAGNOSIS -->
        <div class="flex flex-col md:col-span-2">
          <label class="text-sm font-medium text-gray-700">
            Diagnosis
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 whitespace-pre-wrap">
            {{ visit?.diagnosis || '-' }}
          </div>
        </div>

        <!-- NOTES -->
        <div class="flex flex-col md:col-span-2">
          <label class="text-sm font-medium text-gray-700">
            Notes
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 whitespace-pre-wrap">
            {{ visit?.notes || '-' }}
          </div>
        </div>

        <!-- STATUS -->
        <div class="flex flex-col">
          <label class="text-sm font-medium text-gray-700">
            Status
          </label>

          <div class="mt-2">
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
              :class="statusClass(visit?.status)"
            >
              {{ formatStatus(visit?.status) }}
            </span>
          </div>
        </div>

        <!-- DATE -->
        <div class="flex flex-col">
          <label class="text-sm font-medium text-gray-700">
            Visited At
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800">
            {{ formatDate(visit?.visited_at) }}
          </div>
        </div>

        <!-- Created BS -->
        <div class="flex flex-col">
          <label class="text-sm font-medium text-gray-700">
            Created (BS)
          </label>

          <div class="mt-2 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 font-mono">
            {{ visit?.created_at_bs || '-' }}
          </div>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="mt-8 flex justify-end border-t border-gray-100 pt-6">
        <button
          @click="$emit('close')"
          class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium"
        >
          Close
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
const emit = defineEmits(['close'])

const props = defineProps({
  visit: Object
})

/**
 * FORMAT DATE
 */
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString()
}

/**
 * FORMAT STATUS
 */
const formatStatus = (status) => {
  if (!status) return '-'
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

/**
 * STATUS CLASS
 */
const statusClass = (status) => {
  switch (status) {
    case 'waiting':
      return 'bg-yellow-100 text-yellow-700'
    case 'in_consultation':
      return 'bg-blue-100 text-blue-700'
    case 'completed':
      return 'bg-green-100 text-green-700'
    case 'cancelled':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
}
</script>