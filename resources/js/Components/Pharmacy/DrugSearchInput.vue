<template>
  <div class="relative" ref="containerRef">

    <!-- Search Input -->
    <div class="relative">
      <MagnifyingGlassIcon
        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
      />
      <input
        ref="inputRef"
        v-model="query"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'w-full pl-9 pr-10 py-2.5 text-sm rounded-lg border bg-white shadow-sm',
          'placeholder-slate-400 outline-none transition-all',
          isFocused
            ? 'border-teal-500 ring-2 ring-teal-500/20'
            : 'border-slate-200 hover:border-slate-300',
          disabled ? 'opacity-50 cursor-not-allowed bg-slate-50' : '',
        ]"
        autocomplete="off"
        @focus="onFocus"
        @blur="onBlur"
        @input="onInput"
        @keydown.down.prevent="moveDown"
        @keydown.up.prevent="moveUp"
        @keydown.enter.prevent="selectHighlighted"
        @keydown.escape="close"
      />

      <!-- Spinner / Clear button -->
      <span class="absolute right-3 top-1/2 -translate-y-1/2">
        <button
          v-if="query && !loading"
          type="button"
          @mousedown.prevent="clear"
          class="text-slate-400 hover:text-slate-600 transition"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
        <svg
          v-else-if="loading"
          class="w-4 h-4 text-teal-500 animate-spin"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
      </span>
    </div>

    <!-- Results Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-1 w-full bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden"
        style="min-width: 360px;"
      >
        <!-- Results list -->
        <ul v-if="results.length" role="listbox" class="max-h-80 overflow-y-auto py-1">
          <li
            v-for="(med, index) in results"
            :key="med.id"
            role="option"
            :aria-selected="index === highlightedIndex"
            :class="[
              'flex items-start justify-between gap-3 px-4 py-3 cursor-pointer transition-colors select-none',
              index === highlightedIndex ? 'bg-teal-50' : 'hover:bg-slate-50',
              med.stock_status === 'out_of_stock' ? 'opacity-50 pointer-events-none' : '',
            ]"
            @mousedown.prevent="selectMedicine(med)"
            @mouseover="highlightedIndex = index"
          >
            <!-- Left: name + details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-semibold text-slate-800 text-sm">{{ med.name }}</span>
                <span v-if="med.strength" class="text-xs text-slate-500">{{ med.strength }}</span>
                <FormBadge :form="med.form" size="xs" />
              </div>
              <div class="text-xs text-slate-400 mt-0.5">
                {{ med.generic }}
                <span v-if="med.unit" class="ml-1">· {{ med.unit }}</span>
              </div>
            </div>

            <!-- Right: stock + price -->
            <div class="text-right shrink-0">
              <div class="font-mono font-semibold text-sm text-slate-900">
                Rs {{ formatPrice(med.sale_price) }}
              </div>
              <StockBadge :status="med.stock_status" :count="med.total_stock" :show-count="true" />
            </div>
          </li>
        </ul>

        <!-- Empty state -->
        <div
          v-else-if="!loading && query.length >= 2"
          class="px-4 py-8 text-center text-sm text-slate-400"
        >
          <BeakerIcon class="w-8 h-8 mx-auto mb-2 opacity-30" />
          No medicines found for "{{ query }}"
        </div>

        <!-- Loading state -->
        <div v-else-if="loading" class="px-4 py-6 text-center text-sm text-slate-400">
          Searching…
        </div>

        <!-- Hint -->
        <div
          v-if="results.length"
          class="border-t border-slate-100 px-4 py-2 flex items-center justify-between"
        >
          <span class="text-xs text-slate-400">↑↓ navigate · Enter select · Esc close</span>
          <span class="text-xs text-slate-400">{{ results.length }} result{{ results.length !== 1 ? 's' : '' }}</span>
        </div>

      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { MagnifyingGlassIcon, XMarkIcon, BeakerIcon } from '@heroicons/vue/24/outline'
import StockBadge from '@/Components/Pharmacy/StockBadge.vue'
import FormBadge  from '@/Components/Pharmacy/FormBadge.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  placeholder: { type: String,  default: 'Search by name, barcode, generic…' },
  disabled:    { type: Boolean, default: false },
  searchUrl:   { type: String,  default: '/pharmacy/medicines-search' },
  minChars:    { type: Number,  default: 2 },
})

// ── Emits ──────────────────────────────────────────────────────────
const emit = defineEmits(['select', 'clear'])

// ── State ──────────────────────────────────────────────────────────
const query            = ref('')
const results          = ref([])
const isOpen           = ref(false)
const isFocused        = ref(false)
const loading          = ref(false)
const highlightedIndex = ref(-1)
const inputRef         = ref(null)
const containerRef     = ref(null)
let   debounceTimer    = null

// ── Methods ────────────────────────────────────────────────────────
function onFocus() {
  isFocused.value = true
  if (results.value.length) isOpen.value = true
}

function onBlur() {
  isFocused.value = false
  setTimeout(() => { isOpen.value = false }, 150)
}

function onInput() {
  highlightedIndex.value = -1

  if (query.value.length < props.minChars) {
    results.value = []
    isOpen.value  = false
    return
  }

  clearTimeout(debounceTimer)
  loading.value = true

  debounceTimer = setTimeout(async () => {
    try {
      const url = `${props.searchUrl}?q=${encodeURIComponent(query.value)}`
      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
      results.value = await res.json()
      isOpen.value  = true
    } catch (e) {
      results.value = []
    } finally {
      loading.value = false
    }
  }, 300)
}

function selectMedicine(med) {
  if (med.stock_status === 'out_of_stock') return
  query.value   = `${med.name}${med.strength ? ' ' + med.strength : ''}`
  isOpen.value  = false
  results.value = []
  emit('select', med)
}

function selectHighlighted() {
  const med = results.value[highlightedIndex.value]
  if (med) selectMedicine(med)
}

function moveDown() {
  if (!isOpen.value && results.value.length) { isOpen.value = true; return }
  highlightedIndex.value = Math.min(highlightedIndex.value + 1, results.value.length - 1)
}

function moveUp() {
  highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
}

function close() {
  isOpen.value           = false
  highlightedIndex.value = -1
}

function clear() {
  query.value            = ''
  results.value          = []
  isOpen.value           = false
  highlightedIndex.value = -1
  emit('clear')
  inputRef.value?.focus()
}

// ── Focus API (for parent components) ─────────────────────────────
function focus() { inputRef.value?.focus() }
defineExpose({ focus, clear })

// ── Click outside ──────────────────────────────────────────────────
function handleOutsideClick(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    close()
  }
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick))
onUnmounted(() => {
  document.removeEventListener('mousedown', handleOutsideClick)
  clearTimeout(debounceTimer)
})

// ── Formatters ─────────────────────────────────────────────────────
function formatPrice(val) {
  return Number(val ?? 0).toFixed(2)
}
</script>