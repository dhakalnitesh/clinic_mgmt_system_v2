<template>
  <!-- Renders as a Link (Inertia) if href is given, otherwise a button -->
  <component
    :is="href ? Link : 'button'"
    v-bind="href ? { href } : { type: 'button' }"
    :class="[
      'flex w-full items-center gap-2.5 px-3 py-2 text-sm transition-colors',
      danger
        ? 'text-red-600 hover:bg-red-50'
        : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900',
    ]"
    @click="!href ? $emit('click') : null"
  >
    <!-- Icon slot -->
    <span class="w-4 h-4 shrink-0 text-current opacity-70">
      <slot name="icon">
        <!-- Fallback icon based on `icon` prop -->
        <EyeIcon        v-if="icon === 'eye'" />
        <PencilIcon     v-else-if="icon === 'edit'" />
        <TrashIcon      v-else-if="icon === 'trash'" />
        <AdjustmentsHorizontalIcon v-else-if="icon === 'adjustments'" />
        <ArrowPathIcon  v-else-if="icon === 'toggle'" />
        <DocumentIcon   v-else-if="icon === 'document'" />
        <PrinterIcon    v-else-if="icon === 'print'" />
        <CheckIcon      v-else-if="icon === 'check'" />
      </slot>
    </span>
    <slot />
  </component>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
  EyeIcon, PencilIcon, TrashIcon,
  AdjustmentsHorizontalIcon, ArrowPathIcon,
  DocumentIcon, PrinterIcon, CheckIcon,
} from '@heroicons/vue/20/solid'

defineProps({
  href:   { type: String,  default: '' },
  icon:   { type: String,  default: '' },
  danger: { type: Boolean, default: false },
})

defineEmits(['click'])
</script>