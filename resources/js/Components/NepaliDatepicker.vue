<template>
  <div class="relative">
    <input
      type="text"
      ref="inputRef"
      :value="modelValue"
      :placeholder="placeholder"
      readonly
      class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500 cursor-pointer"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  placeholder: { type: String, default: 'Select Date (BS)' },
  minDate: { type: String, default: '' },
  maxDate: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const inputRef = ref(null)
let pickerInstance = null

function initPicker() {
  if (!inputRef.value || typeof $ === 'undefined') return

  const options = {
    dateFormat: 'YYYY-MM-DD',
    onSelect: function (dateObj) {
      emit('update:modelValue', dateObj.value)
    },
  }

  if (props.modelValue) options.value = props.modelValue
  if (props.minDate) options.minDate = props.minDate
  if (props.maxDate) options.maxDate = props.maxDate

  $(inputRef.value).nepaliDatePicker(options)
  pickerInstance = $(inputRef.value).data('nepaliDatePicker')
}

onMounted(() => {
  initPicker()
})

onUnmounted(() => {
  if (pickerInstance) {
    $(inputRef.value).nepaliDatePicker('destroy')
    pickerInstance = null
  }
})

watch(() => props.minDate, () => {
  if (pickerInstance) {
    $(inputRef.value).nepaliDatePicker('destroy')
    initPicker()
  }
})

watch(() => props.maxDate, () => {
  if (pickerInstance) {
    $(inputRef.value).nepaliDatePicker('destroy')
    initPicker()
  }
})
</script>
