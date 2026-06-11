<template>
  <button
    type="button"
    role="switch"
    :aria-checked="modelValue"
    @click="$emit('update:modelValue', !modelValue)"
    :class="[
      'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent',
      'transition-colors duration-200 ease-in-out',
      'focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2',
      modelValue ? 'bg-teal-600' : 'bg-slate-200',
      disabled ? 'opacity-50 cursor-not-allowed' : '',
    ]"
    :disabled="disabled"
  >
    <span class="sr-only">{{ label }}</span>
    <span
      :class="[
        'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md',
        'transform transition duration-200 ease-in-out ring-0',
        modelValue ? 'translate-x-5' : 'translate-x-0',
      ]"
    />
  </button>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  disabled:   { type: Boolean, default: false },
  label:      { type: String, default: 'Toggle' },
})

defineEmits(['update:modelValue'])
</script>