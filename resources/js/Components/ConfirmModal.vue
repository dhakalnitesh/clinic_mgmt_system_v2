<template>
  <Modal :model-value="modelValue" :title="title" max-width="sm" @update:model-value="cancel">
    <!-- Icon -->
    <div class="flex flex-col items-center text-center gap-4">
      <div :class="['w-12 h-12 rounded-full flex items-center justify-center', iconBg]">
        <svg :class="['w-6 h-6', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
      </div>

      <p class="text-sm text-slate-600 leading-relaxed">{{ message }}</p>
    </div>

    <!-- Actions -->
    <div class="flex gap-3 mt-6">
      <button
        type="button"
        @click="cancel"
        class="flex-1 px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors"
      >
        {{ cancelLabel }}
      </button>
      <button
        type="button"
        @click="confirm"
        :class="['flex-1 px-4 py-2.5 text-sm font-semibold text-white rounded-lg transition-colors shadow-sm', confirmClass]"
      >
        {{ confirmLabel }}
      </button>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  modelValue:   { type: Boolean, default: false },
  title:        { type: String, default: 'Are you sure?' },
  message:      { type: String, default: 'This action cannot be undone.' },
  confirmLabel: { type: String, default: 'Confirm' },
  cancelLabel:  { type: String, default: 'Cancel' },
  confirmClass: { type: String, default: 'bg-teal-600 hover:bg-teal-700' },
  type:         { type: String, default: 'warning' }, // warning | danger | info
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const iconMap = {
  warning: { bg: 'bg-amber-100', color: 'text-amber-600' },
  danger:  { bg: 'bg-red-100',   color: 'text-red-600' },
  info:    { bg: 'bg-blue-100',  color: 'text-blue-600' },
}

const iconBg    = computed(() => (iconMap[props.type] ?? iconMap.warning).bg)
const iconColor = computed(() => (iconMap[props.type] ?? iconMap.warning).color)

function confirm() {
  emit('confirm')
  emit('update:modelValue', false)
}

function cancel() {
  emit('cancel')
  emit('update:modelValue', false)
}
</script>