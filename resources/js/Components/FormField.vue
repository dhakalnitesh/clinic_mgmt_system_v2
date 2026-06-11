<template>
  <div :class="className">
    <!-- Label -->
    <label v-if="label" :for="fieldId" class="block text-sm font-medium text-slate-700 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Input slot -->
    <div class="relative">
      <slot />
    </div>

    <!-- Error message -->
    <p v-if="error" class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
      <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      {{ error }}
    </p>

    <!-- Hint slot (shown when no error) -->
    <div v-else-if="$slots.hint" class="mt-1.5 text-xs text-slate-500">
      <slot name="hint" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label:     { type: String, default: '' },
  error:     { type: String, default: '' },
  required:  { type: Boolean, default: false },
  class:     { type: String, default: '' },
})

// Generate a stable ID for label <-> input association
const fieldId = computed(() =>
  props.label ? props.label.toLowerCase().replace(/\s+/g, '-') : undefined
)

const className = computed(() => props.class || '')
</script>