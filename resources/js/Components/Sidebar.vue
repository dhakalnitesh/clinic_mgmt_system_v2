<template>
    <transition name="slide">
        <aside
            v-if="isMobile && mobileOpen"
            class="fixed left-0 top-16 z-50 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 w-64 flex flex-col border-r border-gray-200 dark:border-gray-700 lg:hidden shadow-xl"
            style="height: calc(100vh - 4rem); overflow-y: auto;"
        >
            <SidebarContent :collapsed="false" :is-mobile="true" @close="$emit('close-mobile')" />
        </aside>
    </transition>

    <aside
        class="hidden lg:flex flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 fixed left-0 top-16 z-40 transition-all duration-300 ease-in-out shadow-sm"
        :class="collapsed ? 'w-16' : 'w-64'"
        style="height: calc(100vh - 4rem); overflow-y: auto;"
    >
        <SidebarContent :collapsed="collapsed" :is-mobile="false" @toggle="$emit('toggle')" />
    </aside>
</template>

<script setup>
import SidebarContent from '@/Components/SidebarContent.vue'

defineProps({
    collapsed: Boolean,
    mobileOpen: Boolean,
    isMobile: Boolean,
})

defineEmits(['toggle', 'close-mobile'])
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active { transition: transform 0.25s ease; }
.slide-enter-from,
.slide-leave-to { transform: translateX(-100%); }
</style>
