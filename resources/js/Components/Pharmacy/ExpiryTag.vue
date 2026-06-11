<template>
  <span :class="tagClass" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium">
    <component :is="icon" class="w-3 h-3 shrink-0" />
    <span>{{ displayDate }}</span>
    <span v-if="showDays" class="opacity-70">({{ daysLabel }})</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { ExclamationTriangleIcon, XCircleIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  date:     { type: String, required: true },   // ISO date string
  status:   { type: String, default: 'good' },  // good | warning | critical | expired
  days:     { type: Number, default: null },
  showDays: { type: Boolean, default: true },
})

const map = {
  good:     { class: 'bg-slate-100 text-slate-600', icon: CheckCircleIcon },
  warning:  { class: 'bg-amber-50 text-amber-700',  icon: ClockIcon },
  critical: { class: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200', icon: ExclamationTriangleIcon },
  expired:  { class: 'bg-red-50 text-red-700 ring-1 ring-red-300',          icon: XCircleIcon },
}

const current     = computed(() => map[props.status] ?? map.good)
const tagClass    = computed(() => current.value.class)
const icon        = computed(() => current.value.icon)

const displayDate = computed(() => {
  if (!props.date) return '—'
  return new Date(props.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
})

const daysLabel = computed(() => {
  if (props.days === null) return ''
  if (props.days < 0)  return `expired ${Math.abs(props.days)}d ago`
  if (props.days === 0) return 'expires today'
  return `${props.days}d left`
})
</script>