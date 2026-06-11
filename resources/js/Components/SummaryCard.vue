<template>
  <div
    :class="[
      'rounded-xl border bg-white p-4 shadow-sm',
      clickable
        ? 'cursor-pointer hover:shadow-md hover:border-teal-300 transition-all active:scale-[0.98]'
        : 'border-slate-200',
    ]"
    @click="clickable ? $emit('click') : null"
  >
    <div class="flex items-start justify-between gap-2">
      <div class="min-w-0">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide truncate">{{ label }}</p>
        <p :class="['mt-1 text-2xl font-bold font-mono tracking-tight', valueColor]">
          {{ formatted }}
        </p>
      </div>
      <div :class="['p-2 rounded-lg shrink-0', iconBg]">
        <!-- Icon: pills -->
        <svg v-if="icon === 'pills'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
        </svg>
        <!-- Icon: check -->
        <svg v-else-if="icon === 'check'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <!-- Icon: warning / exclamation -->
        <svg v-else-if="icon === 'warning'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <!-- Icon: clock -->
        <svg v-else-if="icon === 'clock'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <!-- Icon: x-circle -->
        <svg v-else-if="icon === 'x-circle'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <!-- Icon: beaker -->
        <svg v-else-if="icon === 'beaker'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15M14.25 3.104c.251.023.501.05.75.082M19.8 15a2.25 2.25 0 01-.659 1.591l-1.591 1.591a2.25 2.25 0 01-3.182 0l-5.864-5.864M19.8 15l-5.864-5.864"/>
        </svg>
        <!-- Icon: currency -->
        <svg v-else-if="icon === 'currency'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
    </div>

    <!-- Optional subtitle slot -->
    <div v-if="$slots.default" class="mt-2 text-xs text-slate-400">
      <slot />
    </div>

    <!-- Clickable hint -->
    <p v-if="clickable" class="mt-1.5 text-xs text-teal-600 font-medium">View details →</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label:     { type: String, required: true },
  value:     { type: [Number, String], default: 0 },
  icon:      { type: String, default: 'check' },
  color:     { type: String, default: 'slate' }, // slate | teal | amber | orange | red | emerald
  clickable: { type: Boolean, default: false },
  currency:  { type: Boolean, default: false },
})

defineEmits(['click'])

const colorMap = {
  slate:   { icon: 'text-slate-500',   bg: 'bg-slate-100',   value: 'text-slate-900' },
  teal:    { icon: 'text-teal-600',    bg: 'bg-teal-50',     value: 'text-teal-700' },
  amber:   { icon: 'text-amber-500',   bg: 'bg-amber-50',    value: 'text-amber-700' },
  orange:  { icon: 'text-orange-500',  bg: 'bg-orange-50',   value: 'text-orange-700' },
  red:     { icon: 'text-red-500',     bg: 'bg-red-50',      value: 'text-red-700' },
  emerald: { icon: 'text-emerald-600', bg: 'bg-emerald-50',  value: 'text-emerald-700' },
}

const palette    = computed(() => colorMap[props.color] ?? colorMap.slate)
const iconColor  = computed(() => palette.value.icon)
const iconBg     = computed(() => palette.value.bg)
const valueColor = computed(() => palette.value.value)

const formatted  = computed(() => {
  if (props.currency) {
    return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(props.value ?? 0)
  }
  return (props.value ?? 0).toLocaleString()
})
</script>