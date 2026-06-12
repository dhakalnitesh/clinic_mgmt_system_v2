<template>
    <Teleport to="body">
        <transition name="toast-slide">
            <div
                v-if="visible"
                :class="[toastClass]"
                class="fixed top-4 right-4 z-[9999] flex items-center gap-3 rounded-xl px-5 py-4 shadow-2xl min-w-[320px] max-w-md"
                role="alert"
            >
                <i :class="iconClass" class="text-lg"></i>
                <p class="flex-1 text-sm font-medium">{{ message }}</p>
                <button @click="dismiss" class="text-white/70 hover:text-white transition shrink-0">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const visible = ref(false)
const message = ref('')
const type = ref('success')
let timer = null

const toastClass = computed(() => ({
    success: 'bg-emerald-600 text-white',
    error: 'bg-red-600 text-white',
    warning: 'bg-amber-500 text-white',
    info: 'bg-indigo-600 text-white',
}[type.value] || 'bg-emerald-600 text-white'))

const iconClass = computed(() => ({
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle',
}[type.value] || 'fas fa-check-circle'))

function show(msg, t = 'success') {
    if (timer) clearTimeout(timer)
    message.value = msg
    type.value = t
    visible.value = true
    timer = setTimeout(() => {
        visible.value = false
    }, 5000)
}

function dismiss() {
    visible.value = false
    if (timer) clearTimeout(timer)
}

watch(() => page.props.flash, (flash) => {
    if (flash?.success) show(flash.success, 'success')
    else if (flash?.error) show(flash.error, 'error')
    else if (flash?.warning) show(flash.warning, 'warning')
    else if (flash?.info) show(flash.info, 'info')
}, { deep: true, immediate: true })

onUnmounted(() => {
    if (timer) clearTimeout(timer)
})
</script>

<style scoped>
.toast-slide-enter-active {
    transition: all 0.3s ease-out;
}
.toast-slide-leave-active {
    transition: all 0.2s ease-in;
}
.toast-slide-enter-from {
    opacity: 0;
    transform: translateX(100px);
}
.toast-slide-leave-to {
    opacity: 0;
    transform: translateX(100px);
}
</style>
