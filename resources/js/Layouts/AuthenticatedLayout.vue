<template>
    <div class="flex flex-col h-screen bg-background-light dark:bg-background-dark">
        <Navbar
            @toggle-sidebar="toggleSidebar"
            :sidebar-collapsed="sidebarCollapsed"
            :mobile-sidebar-open="mobileSidebarOpen"
        />

        <div class="flex flex-1 pt-16 overflow-hidden">
            <!-- overlay -->
            <transition name="fade">
                <div
                    v-if="mobileSidebarOpen && isMobile"
                    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                    @click="closeMobileSidebar"
                ></div>
            </transition>

            <Sidebar
                :collapsed="sidebarCollapsed"
                :mobile-open="mobileSidebarOpen"
                :is-mobile="isMobile"
                @toggle="toggleSidebar"
                @close-mobile="closeMobileSidebar"
            />

            <main
                :class="[
          'flex-1 flex flex-col overflow-y-auto transition-all duration-300 ease-in-out bg-gray-50 dark:bg-gray-900',
          sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64'
        ]"
            >
                <div class="p-4 w-full max-w-7xl mx-auto">
                    <slot />
                </div>

                <Footer />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

import Navbar from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'
import Footer from '@/Components/Footer.vue'

usePage()

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const isMobile = computed(() => windowWidth.value < 1024)

const updateBodyOverflow = () => {
    if (typeof document === 'undefined') return
    document.body.style.overflow = (isMobile.value && mobileSidebarOpen.value) ? 'hidden' : ''
}

const toggleSidebar = () => {
    if (isMobile.value) {
        mobileSidebarOpen.value = !mobileSidebarOpen.value
        updateBodyOverflow()
    } else {
        sidebarCollapsed.value = !sidebarCollapsed.value
    }
}

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false
    updateBodyOverflow()
}

const handleResize = () => {
    windowWidth.value = window.innerWidth
    if (!isMobile.value && mobileSidebarOpen.value) closeMobileSidebar()
    if (isMobile.value) sidebarCollapsed.value = false
}

const handleEscapeKey = (e) => {
    if (e.key === 'Escape' && mobileSidebarOpen.value) closeMobileSidebar()
}

onMounted(() => {
    window.addEventListener('resize', handleResize)
    document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
    document.removeEventListener('keydown', handleEscapeKey)
    if (typeof document !== 'undefined') document.body.style.overflow = ''
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
</style>
