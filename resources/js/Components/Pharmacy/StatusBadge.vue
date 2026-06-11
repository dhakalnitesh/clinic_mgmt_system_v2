<template>
  <span :class="badgeClass" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold">
    <span :class="dotClass" class="w-1.5 h-1.5 rounded-full shrink-0" />
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
  type:   { type: String, default: 'po' }, // po | grn | supplier_return
})

const maps = {
  po: {
    draft:      { badge: 'bg-slate-100  text-slate-600',  dot: 'bg-slate-400',   label: 'Draft'      },
    sent:       { badge: 'bg-blue-50    text-blue-700',   dot: 'bg-blue-500',    label: 'Sent'       },
    partial:    { badge: 'bg-amber-50   text-amber-700',  dot: 'bg-amber-500',   label: 'Partial'    },
    received:   { badge: 'bg-emerald-50 text-emerald-700',dot: 'bg-emerald-500', label: 'Received'   },
    cancelled:  { badge: 'bg-red-50     text-red-600',    dot: 'bg-red-400',     label: 'Cancelled'  },
  },
  grn: {
    pending:    { badge: 'bg-amber-50   text-amber-700',  dot: 'bg-amber-500',   label: 'Pending'    },
    verified:   { badge: 'bg-blue-50    text-blue-700',   dot: 'bg-blue-500',    label: 'Verified'   },
    posted:     { badge: 'bg-emerald-50 text-emerald-700',dot: 'bg-emerald-500', label: 'Posted'     },
  },
  supplier_return: {
    draft:      { badge: 'bg-slate-100  text-slate-600',  dot: 'bg-slate-400',   label: 'Draft'      },
    sent:       { badge: 'bg-blue-50    text-blue-700',   dot: 'bg-blue-500',    label: 'Sent'       },
    completed:  { badge: 'bg-emerald-50 text-emerald-700',dot: 'bg-emerald-500', label: 'Completed'  },
    cancelled:  { badge: 'bg-red-50     text-red-600',    dot: 'bg-red-400',     label: 'Cancelled'  },
  },
}

const map       = computed(() => maps[props.type] ?? maps.po)
const current   = computed(() => map.value[props.status] ?? { badge: 'bg-slate-100 text-slate-500', dot: 'bg-slate-400', label: props.status })
const badgeClass= computed(() => current.value.badge)
const dotClass  = computed(() => current.value.dot)
const label     = computed(() => current.value.label)
</script>