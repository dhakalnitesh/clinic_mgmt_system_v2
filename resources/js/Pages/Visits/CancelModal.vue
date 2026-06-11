<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>

    <!-- Modal content -->
    <div class="relative bg-white w-full max-w-md rounded-xl shadow-2xl p-6">

      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-red-600">Cancel Visit</h3>
        <button class="text-gray-500 hover:text-gray-700 text-2xl" @click="$emit('close')">&times;</button>
      </div>

      <!-- Body -->
      <div class="text-center space-y-4">
        <div class="text-red-600 text-4xl">
          <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h3 class="text-lg font-semibold">
          Are you sure you want to cancel <b>{{ visit.patient?.name || 'this appointment' }}</b>?
        </h3>

        <p class="text-gray-600">
          This action will mark the visit as cancelled.
        </p>

        <!-- Buttons -->
        <div class="flex justify-center gap-4 pt-4">
          <button class="px-4 py-2 rounded-lg border" @click="$emit('close')">
            No
          </button>

          <button class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white" :disabled="processing"
            @click="submit">
            {{ processing ? "Cancelling..." : "Yes, Cancel" }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  visit: { type: Object, required: true },
});

const emit = defineEmits(["close"]);

const processing = ref(false);

const submit = () => {
  if (processing.value) return;

  processing.value = true;

  router.patch(`/visits/${props.visit.id}/cancel`, {}, {
  preserveScroll: true,
  onSuccess: () => {
    processing.value = false
    emit("close")
    props.visit.status = 'cancelled'
  }
})
};
</script>