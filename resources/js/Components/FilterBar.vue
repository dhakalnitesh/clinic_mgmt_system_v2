<template>
  <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
      <!-- Search -->
      <div class="flex-1 min-w-[200px]">
        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Search</label>
        <input
          v-model="search"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500"
        />
      </div>

      <!-- Start Date -->
      <div class="w-[140px]">
        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Start Date (BS)</label>
        <NepaliDatepicker v-model="startDate" placeholder="Start" />
      </div>

      <!-- End Date -->
      <div class="w-[140px]">
        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">End Date (BS)</label>
        <NepaliDatepicker v-model="endDate" placeholder="End" />
      </div>

      <!-- Rows -->
      <div class="w-[80px]">
        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Rows</label>
        <select
          v-model="perPage"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-2 text-sm text-gray-900 dark:text-gray-100"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>

      <!-- Buttons -->
      <div class="flex gap-2">
        <button
          @click="apply"
          class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition-colors"
        >
          Apply
        </button>
        <button
          @click="reset"
          class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
          Reset
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import NepaliDatepicker from '@/Components/NepaliDatepicker.vue'

const props = defineProps({
  routeName: { type: String, required: true },
  filters: { type: Object, default: () => ({}) },
  searchPlaceholder: { type: String, default: 'Search...' },
  preserveState: { type: Boolean, default: true },
})

const search = ref(props.filters?.search || '')
const startDate = ref(props.filters?.start_date || props.filters?.startDate || '')
const endDate = ref(props.filters?.end_date || props.filters?.endDate || '')
const perPage = ref(props.filters?.per_page || props.filters?.perPage || 10)

const apply = () => {
  router.get(
    route(props.routeName),
    {
      search: search.value,
      start_date: startDate.value,
      end_date: endDate.value,
      per_page: perPage.value,
    },
    {
      preserveState: props.preserveState,
      replace: true,
    }
  )
}

const reset = () => {
  search.value = ''
  startDate.value = ''
  endDate.value = ''
  perPage.value = 10

  router.get(
    route(props.routeName),
    {},
    {
      preserveState: props.preserveState,
      replace: true,
    }
  )
}
</script>