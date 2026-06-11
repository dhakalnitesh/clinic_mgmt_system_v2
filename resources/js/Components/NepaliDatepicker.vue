<template>
  <div class="relative">
    <input
      type="text"
      readonly
      :value="modelValue"
      :placeholder="placeholder"
      @click="showPicker = !showPicker"
      class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2.5 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500 cursor-pointer"
    />
    <div v-if="showPicker" class="absolute z-50 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg p-3 w-72">
      <div class="flex gap-2 mb-2">
        <select v-model="year" class="flex-1 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <select v-model="month" class="flex-1 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
          <option v-for="(m, i) in monthNames" :key="i" :value="i + 1">{{ m }}</option>
        </select>
      </div>
      <div class="grid grid-cols-7 gap-1 text-center text-xs mb-1">
        <div v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="font-semibold text-gray-500 dark:text-gray-400 py-1">{{ d }}</div>
      </div>
      <div class="grid grid-cols-7 gap-1 text-center text-xs">
        <template v-for="(day, i) in calendarDays" :key="i">
          <div v-if="day === 0" class="py-1"></div>
          <button v-else @click="selectDate(day)"
            :class="[
              'py-1 rounded hover:bg-teal-100 dark:hover:bg-teal-900 transition',
              day === selectedDay ? 'bg-teal-600 text-white hover:bg-teal-700' : 'text-gray-700 dark:text-gray-300'
            ]">{{ day }}</button>
        </template>
      </div>
      <div class="flex justify-between mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
        <button @click="showPicker = false" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
        <button @click="today" class="text-xs text-teal-600 hover:text-teal-700 font-medium">Today</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  placeholder: { type: String, default: 'Select Date (BS)' },
  yearCount: { type: Number, default: 10 },
})

const emit = defineEmits(['update:modelValue'])

const showPicker = ref(false)
const year = ref(2080)
const month = ref(1)
const selectedDay = ref(null)
const startDayOfWeek = ref(0)

const currentBsYear = 2083
const years = computed(() => {
  const y = []
  for (let i = props.yearCount - 1; i >= 0; i--) {
    y.push(currentBsYear - i)
  }
  return y
})

const monthNames = ['Baisakh','Jestha','Ashad','Shrawan','Bhadra','Ashwin','Kartik','Mangsir','Poush','Magh','Falgun','Chaitra']

const monthDays = [31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30]

const calendarDays = computed(() => {
  const days = []
  const totalDays = monthDays[month.value - 1] || 30
  for (let i = 0; i < startDayOfWeek.value; i++) {
    days.push(0)
  }
  for (let d = 1; d <= totalDays; d++) {
    days.push(d)
  }
  return days
})

const selectDate = (day) => {
  selectedDay.value = day
  const m = String(month.value).padStart(2, '0')
  const d = String(day).padStart(2, '0')
  emit('update:modelValue', `${year.value}-${m}-${d}`)
  showPicker.value = false
}

const today = () => {
  year.value = currentBsYear
  month.value = 1
  selectedDay.value = 1
  emit('update:modelValue', `${currentBsYear}-01-01`)
  showPicker.value = false
}

watch(showPicker, (val) => {
  if (val && props.modelValue) {
    const parts = String(props.modelValue).split('-')
    if (parts.length === 3) {
      year.value = parseInt(parts[0]) || currentBsYear
      month.value = parseInt(parts[1]) || 1
      selectedDay.value = parseInt(parts[2]) || null
    }
  }
})
</script>
