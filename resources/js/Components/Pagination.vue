<template>
  <nav v-if="links && links.length > 3" class="inline-flex items-center gap-1">
    <template v-for="(link, i) in links" :key="i">
      <!-- Previous / Next arrows -->
      <Link
        v-if="link.url && (link.label.includes('Previous') || link.label.includes('Next'))"
        :href="link.url"
        preserve-scroll
        :class="[
          'inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm border transition-all duration-150',
          'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500',
        ]"
      >
        <i :class="link.label.includes('Previous') ? 'fas fa-chevron-left' : 'fas fa-chevron-right'" class="text-xs"></i>
      </Link>

      <!-- Disabled prev/next -->
      <span
        v-else-if="!link.url && (link.label.includes('Previous') || link.label.includes('Next'))"
        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm border bg-gray-50 dark:bg-gray-800 text-gray-300 dark:text-gray-600 border-gray-200 dark:border-gray-700 cursor-not-allowed"
      >
        <i :class="link.label.includes('Previous') ? 'fas fa-chevron-left' : 'fas fa-chevron-right'" class="text-xs"></i>
      </span>

      <!-- Ellipsis -->
      <span
        v-else-if="link.label === '...'"
        class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 dark:text-gray-500"
      >…</span>

      <!-- Numbered page -->
      <Link
        v-else-if="link.url"
        :href="link.url"
        preserve-scroll
        :class="[
          'inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm border transition-all duration-150 font-medium',
          link.active
            ? 'bg-teal-600 text-white border-teal-600 shadow-sm'
            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
        ]"
      >{{ link.label }}</Link>

      <!-- Current page (no URL) -->
      <span
        v-else
        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm border bg-teal-600 text-white border-teal-600 font-semibold shadow-sm"
      >{{ link.label }}</span>
    </template>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  links: { type: Array, default: () => [] },
})
</script>