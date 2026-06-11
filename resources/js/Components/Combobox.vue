<template>
  <div class="relative" ref="containerRef">
    <!-- Input -->
    <div class="relative">
      <input
        ref="inputRef"
        v-model="query"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'form-input pr-8',
          error ? 'form-input-error' : '',
          disabled ? 'opacity-50 cursor-not-allowed' : '',
        ]"
        @focus="onFocus"
        @blur="onBlur"
        @input="onInput"
        @keydown.down.prevent="moveDown"
        @keydown.up.prevent="moveUp"
        @keydown.enter.prevent="selectHighlighted"
        @keydown.escape="close"
        autocomplete="off"
      />
      <!-- Chevron / Spinner -->
      <span class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none">
        <svg v-if="loading" class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        <svg v-else class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </span>
    </div>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen && filteredOptions.length"
        class="absolute z-50 mt-1 w-full bg-white rounded-lg border border-slate-200 shadow-lg max-h-60 overflow-auto"
      >
        <ul role="listbox">
          <li
            v-for="(option, index) in filteredOptions"
            :key="getKey(option)"
            role="option"
            :aria-selected="getKey(option) === modelValue"
            :class="[
              'flex items-center justify-between px-3 py-2.5 text-sm cursor-pointer select-none',
              index === highlightedIndex ? 'bg-teal-50 text-teal-800' : 'text-slate-700 hover:bg-slate-50',
              getKey(option) === modelValue ? 'font-semibold' : '',
            ]"
            @mousedown.prevent="selectOption(option)"
            @mouseover="highlightedIndex = index"
          >
            <span>{{ getLabel(option) }}</span>
            <!-- Checkmark for selected -->
            <svg v-if="getKey(option) === modelValue"
                 class="w-4 h-4 text-teal-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
          </li>
        </ul>
      </div>

      <!-- No results -->
      <div
        v-else-if="isOpen && !loading && query.length >= 1"
        class="absolute z-50 mt-1 w-full bg-white rounded-lg border border-slate-200 shadow-lg px-3 py-4 text-sm text-slate-400 text-center"
      >
        No results for "{{ query }}"
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue:  { default: null },
  options:     { type: Array, default: () => [] },     // local options array
  labelKey:    { type: String, default: 'name' },       // which key to show
  valueKey:    { type: String, default: 'id' },         // which key to emit
  placeholder: { type: String, default: 'Search…' },
  searchUrl:   { type: String, default: '' },           // if set, fetch from API
  disabled:    { type: Boolean, default: false },
  error:       { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

// ── State ──────────────────────────────────────────────────────────
const query            = ref('')
const isOpen           = ref(false)
const loading          = ref(false)
const highlightedIndex = ref(-1)
const asyncOptions     = ref([])
const inputRef         = ref(null)
const containerRef     = ref(null)

// ── Computed ───────────────────────────────────────────────────────
const filteredOptions = computed(() => {
  if (props.searchUrl) return asyncOptions.value

  if (!query.value) return props.options

  return props.options.filter(opt =>
    getLabel(opt).toLowerCase().includes(query.value.toLowerCase())
  )
})

// ── Helpers ────────────────────────────────────────────────────────
function getLabel(opt) {
  return typeof opt === 'object' ? opt[props.labelKey] : opt
}

function getKey(opt) {
  return typeof opt === 'object' ? opt[props.valueKey] : opt
}

// ── On mount: set query from existing value ────────────────────────
onMounted(() => {
  if (props.modelValue) {
    const found = props.options.find(o => getKey(o) === props.modelValue)
    if (found) query.value = getLabel(found)
  }
})

watch(() => props.modelValue, (val) => {
  if (!val) { query.value = ''; return }
  const found = props.options.find(o => getKey(o) === val)
  if (found) query.value = getLabel(found)
})

// ── Events ─────────────────────────────────────────────────────────
function onFocus() {
  isOpen.value = true
  highlightedIndex.value = -1
}

function onBlur() {
  // Small delay so mousedown on option fires first
  setTimeout(() => {
    isOpen.value = false
    // If query doesn't match selected, reset
    if (props.modelValue) {
      const found = props.options.find(o => getKey(o) === props.modelValue)
      if (found) query.value = getLabel(found)
    } else {
      // No selection and user typed something? clear it
    }
  }, 150)
}

let debounceTimer = null
function onInput() {
  isOpen.value = true
  highlightedIndex.value = -1
  emit('update:modelValue', null)

  if (!props.searchUrl) return

  clearTimeout(debounceTimer)
  if (query.value.length < 1) { asyncOptions.value = []; return }

  loading.value = true
  debounceTimer = setTimeout(async () => {
    try {
      const res = await fetch(`${props.searchUrl}?q=${encodeURIComponent(query.value)}`)
      asyncOptions.value = await res.json()
    } catch (e) {
      asyncOptions.value = []
    } finally {
      loading.value = false
    }
  }, 300)
}

function selectOption(option) {
  query.value = getLabel(option)
  emit('update:modelValue', getKey(option))
  isOpen.value = false
}

function selectHighlighted() {
  if (highlightedIndex.value >= 0 && filteredOptions.value[highlightedIndex.value]) {
    selectOption(filteredOptions.value[highlightedIndex.value])
  }
}

function moveDown() {
  if (!isOpen.value) { isOpen.value = true; return }
  highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredOptions.value.length - 1)
}

function moveUp() {
  highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
}

function close() {
  isOpen.value = false
}

// Click outside to close
function handleOutsideClick(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    isOpen.value = false
  }
}
onMounted(() => document.addEventListener('mousedown', handleOutsideClick))
onUnmounted(() => document.removeEventListener('mousedown', handleOutsideClick))
</script>