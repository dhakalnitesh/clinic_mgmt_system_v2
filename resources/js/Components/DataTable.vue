<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          <i :class="icon"></i>
          {{ title }}
        </h1>
        <p v-if="description" class="text-sm text-gray-600">{{ description }}</p>
      </div>
      <slot name="actions" />
    </div>

    <!-- FilterBar slot (or default) -->
    <slot name="filters">
      <FilterBar
        :route-name="routeName"
        :filters="filters"
        :search-placeholder="searchPlaceholder"
      />
    </slot>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">SN</th>
              <slot name="thead" />
              <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <slot name="tbody" />
            <tr v-if="empty">
              <td :colspan="colspan" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                {{ emptyText }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="data?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Showing
          <span class="font-medium">{{ data.from || 0 }}</span>
          to
          <span class="font-medium">{{ data.to || 0 }}</span>
          of
          <span class="font-medium">{{ data.total }}</span>
          results
        </div>
        <Pagination :links="data.links" />
      </div>
    </div>

    <slot name="modals" />
  </div>
</template>

<script setup>
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  icon: { type: String, default: '' },
  routeName: { type: String, required: true },
  filters: { type: Object, default: () => ({}) },
  searchPlaceholder: { type: String, default: 'Search...' },
  data: { type: Object, default: null },
  empty: { type: Boolean, default: false },
  emptyText: { type: String, default: 'No records found.' },
  colspan: { type: [String, Number], default: 7 },
})
</script>