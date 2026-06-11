<template>
  <span :class="[baseClass, sizeClass, colorClass]">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  form: { type: String, required: true },
  size: { type: String, default: 'sm' }, // sm | xs
})

const formMap = {
  tablet:      { label: 'Tab',    color: 'bg-blue-50   text-blue-700'   },
  capsule:     { label: 'Cap',    color: 'bg-purple-50 text-purple-700' },
  syrup:       { label: 'Syrup',  color: 'bg-cyan-50   text-cyan-700'   },
  suspension:  { label: 'Susp',   color: 'bg-cyan-50   text-cyan-700'   },
  injection:   { label: 'Inj',    color: 'bg-red-50    text-red-700'    },
  cream:       { label: 'Cream',  color: 'bg-amber-50  text-amber-700'  },
  ointment:    { label: 'Oint',   color: 'bg-amber-50  text-amber-700'  },
  gel:         { label: 'Gel',    color: 'bg-lime-50   text-lime-700'   },
  drops:       { label: 'Drops',  color: 'bg-teal-50   text-teal-700'   },
  inhaler:     { label: 'Inh',    color: 'bg-indigo-50 text-indigo-700' },
  patch:       { label: 'Patch',  color: 'bg-slate-100 text-slate-600'  },
  suppository: { label: 'Supp',   color: 'bg-orange-50 text-orange-700' },
  powder:      { label: 'Pwd',    color: 'bg-yellow-50 text-yellow-700' },
  lotion:      { label: 'Lot',    color: 'bg-lime-50   text-lime-700'   },
  solution:    { label: 'Sol',    color: 'bg-cyan-50   text-cyan-700'   },
  other:       { label: 'Other',  color: 'bg-slate-100 text-slate-500'  },
}

const baseClass  = 'inline-flex items-center font-medium rounded'
const sizeClass  = computed(() => props.size === 'xs' ? 'text-xs px-1.5 py-0' : 'text-xs px-2 py-0.5')
const colorClass = computed(() => (formMap[props.form] ?? formMap.other).color)
const label      = computed(() => (formMap[props.form] ?? formMap.other).label)
</script>