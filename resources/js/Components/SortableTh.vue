<template>
  <th
    :class="[
      'px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide select-none',
      field ? 'cursor-pointer hover:text-teal-700 hover:bg-slate-100 transition-colors' : '',
    ]"
    @click="field ? $emit('sort', field) : null"
  >
    <span class="inline-flex items-center gap-1">
      <slot />
      <!-- Sort icons -->
      <span v-if="field" class="inline-flex flex-col gap-px">
        <svg
          class="w-2.5 h-2.5 transition-colors"
          :class="isActive && sortDir === 'asc' ? 'text-teal-600' : 'text-slate-300'"
          viewBox="0 0 10 6" fill="currentColor"
        >
          <path d="M5 0L10 6H0L5 0Z"/>
        </svg>
        <svg
          class="w-2.5 h-2.5 transition-colors"
          :class="isActive && sortDir === 'desc' ? 'text-teal-600' : 'text-slate-300'"
          viewBox="0 0 10 6" fill="currentColor"
        >
          <path d="M5 6L0 0H10L5 6Z"/>
        </svg>
      </span>
    </span>
  </th>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  field: { type: String, default: '' },
  sort:  { type: Object, default: () => ({}) }, // filters object containing sort + direction
})

defineEmits(['sort'])

const isActive  = computed(() => props.sort?.sort === props.field)
const sortDir   = computed(() => props.sort?.direction ?? 'asc')
</script>