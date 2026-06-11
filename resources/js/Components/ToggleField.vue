<template>
  <div class="flex items-start justify-between gap-4 py-1">
    <!-- Text -->
    <div class="flex-1 min-w-0">
      <p class="text-sm font-medium text-slate-700">{{ label }}</p>
      <p v-if="description" class="mt-0.5 text-xs text-slate-500">{{ description }}</p>
    </div>

    <!-- Toggle -->
    <Toggle
      :model-value="modelValue"
      :label="label"
      @update:model-value="$emit('update:modelValue', $event)"
    />
  </div>
</template>

<script setup>
import Toggle from '@/Components/Toggle.vue'

defineProps({
  modelValue:  { type: Boolean, default: false },
  label:       { type: String, required: true },
  description: { type: String, default: '' },
})

defineEmits(['update:modelValue'])
</script>