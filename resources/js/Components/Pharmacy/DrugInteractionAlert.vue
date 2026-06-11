<template>
  <div v-if="interactions.length" class="rounded-xl overflow-hidden border shadow-sm"
       :class="hasCritical ? 'border-red-300' : 'border-amber-300'">

    <!-- Header -->
    <div class="flex items-center gap-3 px-4 py-3"
         :class="hasCritical ? 'bg-red-50' : 'bg-amber-50'">
      <ExclamationTriangleIcon :class="['w-5 h-5 shrink-0', hasCritical ? 'text-red-600' : 'text-amber-600']" />
      <div class="flex-1 min-w-0">
        <p :class="['text-sm font-semibold', hasCritical ? 'text-red-800' : 'text-amber-800']">
          Drug Interaction {{ interactions.length > 1 ? 'Alerts' : 'Alert' }}
          <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs font-bold"
                :class="hasCritical ? 'bg-red-200 text-red-900' : 'bg-amber-200 text-amber-900'">
            {{ interactions.length }}
          </span>
        </p>
        <p :class="['text-xs mt-0.5', hasCritical ? 'text-red-600' : 'text-amber-600']">
          Review before dispensing
        </p>
      </div>
      <button type="button" @click="expanded = !expanded"
              :class="['text-xs font-medium underline shrink-0', hasCritical ? 'text-red-600' : 'text-amber-600']">
        {{ expanded ? 'Collapse' : 'View details' }}
      </button>
    </div>

    <!-- Details -->
    <div v-if="expanded" class="divide-y"
         :class="hasCritical ? 'divide-red-100 bg-red-50/40' : 'divide-amber-100 bg-amber-50/40'">
      <div v-for="(ia, idx) in interactions" :key="idx" class="px-4 py-3">
        <div class="flex items-start gap-3">
          <span :class="[
            'shrink-0 mt-0.5 px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide',
            severityClass(ia.severity),
          ]">{{ ia.label }}</span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-800">
              {{ ia.drug_a }} ↔ {{ ia.drug_b }}
            </p>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ ia.description }}</p>
            <p v-if="ia.management" class="text-xs text-slate-500 mt-1 italic leading-relaxed">
              Management: {{ ia.management }}
            </p>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  interactions: { type: Array, default: () => [] },
})

const expanded   = ref(true)
const hasCritical = computed(() =>
  props.interactions.some(i => ['contraindicated', 'major'].includes(i.severity))
)

function severityClass(severity) {
  return {
    contraindicated: 'bg-red-200    text-red-900',
    major:           'bg-orange-200 text-orange-900',
    moderate:        'bg-amber-200  text-amber-800',
    minor:           'bg-blue-100   text-blue-800',
  }[severity] ?? 'bg-slate-100 text-slate-600'
}
</script>