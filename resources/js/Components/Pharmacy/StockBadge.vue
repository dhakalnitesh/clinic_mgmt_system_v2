<template>
  <span :class="badgeClass" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold tracking-wide">
    <span :class="dotClass" class="w-1.5 h-1.5 rounded-full" />
    {{ label }}
    <span v-if="showCount" class="font-mono ml-0.5">{{ count }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status:    { type: String,  required: true }, // in_stock | low_stock | out_of_stock
  count:     { type: Number,  default: null },
  showCount: { type: Boolean, default: false },
})

const map = {
  in_stock:     { badge: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', dot: 'bg-emerald-500', label: 'In Stock'      },
  low_stock:    { badge: 'bg-amber-50  text-amber-700  ring-1 ring-amber-200',     dot: 'bg-amber-500',   label: 'Low Stock'     },
  out_of_stock: { badge: 'bg-red-50    text-red-700    ring-1 ring-red-200',        dot: 'bg-red-500',     label: 'Out of Stock'  },
}

const current    = computed(() => map[props.status] ?? map.in_stock)
const badgeClass = computed(() => current.value.badge)
const dotClass   = computed(() => current.value.dot)
const label      = computed(() => current.value.label)
</script>