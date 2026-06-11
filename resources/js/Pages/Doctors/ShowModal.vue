<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')"></div>

    <!-- Modal content -->
    <div class="relative bg-white w-full max-w-3xl rounded-xl shadow-2xl p-8 overflow-y-auto max-h-[90vh]">

      <!-- Header -->
      <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-3">
        <h3 class="text-2xl font-bold text-indigo-600">Doctor Details</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
      </div>

      <!-- Body -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">

        <!-- Name -->
        <div class="flex flex-col">
          <span class="text-sm font-semibold text-gray-500">Name</span>
          <p class="mt-1 text-gray-900 text-lg">{{ doctor.name || '—' }}</p>
        </div>

        <!-- NMC Number -->
        <div class="flex flex-col">
          <span class="text-sm font-semibold text-gray-500">NMC Number</span>
          <p class="mt-1 text-gray-900 text-lg">{{ doctor.nmc_number || '—' }}</p>
        </div>

        <!-- Specialization -->
        <div class="flex flex-col">
          <span class="text-sm font-semibold text-gray-500">Specialization</span>
          <p class="mt-1 text-gray-900 text-lg">{{ doctor.specialization || '—' }}</p>
        </div>

        <!-- Phone -->
        <div class="flex flex-col">
          <span class="text-sm font-semibold text-gray-500">Phone</span>
          <p class="mt-1 text-gray-900 text-lg">{{ doctor.phone || '—' }}</p>
        </div>

        <!-- Availability Schedule -->
        <div class="flex flex-col md:col-span-2">
          <span class="text-sm font-semibold text-gray-500">Availability Schedule</span>
          <p class="mt-1 text-gray-900 text-lg">{{ doctor.availability_schedule || '—' }}</p>
        </div>

        <!-- Photo -->
        <div class="flex flex-col md:col-span-2">
          <span class="text-sm font-semibold text-gray-500">Photo</span>
          <div class="mt-2">
            <img v-if="doctor.photo_url" :src="doctor.photo_url" alt="Doctor Photo" class="h-32 w-32 object-cover rounded-lg border" />
            <p v-else class="text-gray-400 italic">No photo available</p>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="mt-8 flex justify-end">
        <button @click="$emit('close')" class="px-6 py-2 rounded-lg border text-gray-700 hover:bg-gray-100 font-medium">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  doctor: { type: Object, required: true }
})
</script>