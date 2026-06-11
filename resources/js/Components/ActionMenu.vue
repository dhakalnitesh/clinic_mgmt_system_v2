<template>
  <div class="relative" ref="menuRef">
    <!-- Trigger button -->
    <button
      type="button"
      @click.stop="toggle"
      :class="[
        'inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400',
        'hover:text-slate-600 hover:bg-slate-100 transition-colors',
        'focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-1',
        open ? 'bg-slate-100 text-slate-600' : '',
      ]"
    >
      <span class="sr-only">Open actions</span>
      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
      </svg>
    </button>

    <!-- Dropdown panel -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="open"
        :class="[
          'absolute z-50 min-w-40 bg-white rounded-xl border border-slate-200 shadow-lg py-1',
          alignRight ? 'right-0' : 'left-0',
          'top-full mt-1',
        ]"
        @click="close"
      >
        <slot />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

defineProps({
  alignRight: { type: Boolean, default: true },
})

const open    = ref(false)
const menuRef = ref(null)

function toggle() { open.value = !open.value }
function close()  { open.value = false }

function handleOutside(e) {
  if (menuRef.value && !menuRef.value.contains(e.target)) {
    close()
  }
}

onMounted(() => document.addEventListener('click', handleOutside))
onUnmounted(() => document.removeEventListener('click', handleOutside))
</script>