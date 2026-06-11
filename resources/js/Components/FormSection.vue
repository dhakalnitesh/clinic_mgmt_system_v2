<template>
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Section Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/60">
      <h3 class="text-sm font-semibold text-slate-800">{{ title }}</h3>
      <p v-if="description" class="mt-0.5 text-xs text-slate-500">{{ description }}</p>
    </div>
    <!-- Section Body -->
    <div class="px-6 py-5">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title:       { type: String, required: true },
  description: { type: String, default: '' },
})
</script>