<template>
    <nav
        class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-6 fixed top-0 left-0 right-0 z-50">
        <!-- Left side -->
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Toggle -->
            <button @click="toggleMobileSidebar"
                class="lg:hidden flex size-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <i :class="mobileSidebarOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
            </button>

            <!-- Desktop Sidebar Toggle -->
            <button @click="toggleDesktopSidebar"
                class="hidden lg:flex size-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                <i :class="sidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
            </button>

            <!-- Brand -->
            <div class="hidden md:flex items-center gap-3">
                <div class="flex items-center justify-center size-8 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 text-white shadow-sm">
                    <i class="fas fa-heartbeat text-sm"></i>
                </div>
                <h2 class="text-lg font-bold leading-tight tracking-tight text-gray-900 dark:text-gray-100">
                    Clinic Management System
                </h2>
            </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-3">
            <!-- Dark Mode Toggle -->
            <button @click="toggleDarkMode"
                class="flex size-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'">
                <i :class="isDark ? 'fas fa-sun' : 'fas fa-moon'"></i>
            </button>

            <!-- Notifications / Help (Desktop only) -->
            <!-- <div class="hidden md:flex gap-2">
                <div class="relative">
                    <button @click="toggleNotifications"
                        class="flex size-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors relative">
                        <i class="fas fa-bell"></i>
                        <span v-if="unreadNotifications > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                        </span>
                    </button>

                    <transition name="slide-down">
                        <div v-if="notificationsOpen"
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                                    <button @click="markAllAsRead" class="text-sm text-teal-600 dark:text-teal-400 font-medium">
                                        Mark all as read
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                <div v-for="notification in notifications" :key="notification.id"
                                    class="p-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 cursor-pointer">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ notification.title }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ notification.message }}</p>
                                </div>

                                <div v-if="notifications.length === 0" class="p-8 text-center">
                                    <i class="fas fa-bell-slash text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-gray-500 dark:text-gray-400">No notifications yet</p>
                                </div>
                            </div>

                            <div class="p-3 border-t border-gray-200 dark:border-gray-700 text-center">
                                <a href="/notifications" class="text-sm text-teal-600 dark:text-teal-400 font-medium">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </transition>
                </div>


            </div> -->

            <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 mx-2 hidden md:block"></div>

            <!-- User Menu -->
            <div class="relative">
                <button @click="toggleUserMenu" ref="userMenuButton"
                    class="flex items-center gap-3 focus:outline-none group">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold leading-none text-gray-900 dark:text-gray-100">{{ userName }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ userRole }}</p>
                    </div>

                    <div class="size-10 rounded-full overflow-hidden border-2 border-gray-300 dark:border-gray-600 bg-center bg-cover"
                        :style="{ backgroundImage: `url(${userAvatar})` }"></div>

                    <i :class="userMenuOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"
                        class="hidden md:block text-gray-500 dark:text-gray-400 text-sm"></i>
                </button>

                <transition name="slide-down">
                    <div v-show="userMenuOpen" ref="userDropdown"
                        class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                        <div class="py-2">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ userName }}</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs truncate">{{ userEmail }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ userRole }}</div>
                            </div>

                            <hr class="my-1 border-gray-200 dark:border-gray-700" />

                            <button @click="showLogoutConfirm = true"
                                class="flex items-center gap-3 w-full text-left px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Log Out</span>
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </nav>

    <!-- Logout Confirmation Modal -->
    <ConfirmModal
        v-model="showLogoutConfirm"
        title="Log Out"
        message="Are you sure you want to log out? You will need to sign in again to access the system."
        confirm-label="Log Out"
        type="warning"
        @confirm="confirmLogout"
    />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const emit = defineEmits(['toggle-sidebar'])

const props = defineProps({
    sidebarCollapsed: Boolean,
    mobileSidebarOpen: Boolean
})

const page = usePage()

const searchQuery = ref('')
const showLogoutConfirm = ref(false)

// Dark mode
const isDark = ref(localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches))

const toggleDarkMode = () => {
    isDark.value = !isDark.value
    if (isDark.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
}

onMounted(() => {
    if (isDark.value) document.documentElement.classList.add('dark')
    
    // Listen for theme changes from other tabs/windows
    window.addEventListener('storage', (e) => {
        if (e.key === 'theme') {
            const isDarkMode = e.newValue === 'dark'
            isDark.value = isDarkMode
            if (isDarkMode) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        }
    })
})
const userMenuOpen = ref(false)
const notificationsOpen = ref(false)
const userMenuButton = ref(null)
const userDropdown = ref(null)

// Safe Ziggy route wrapper (prevents blank component if route() missing)
const safeRoute = (name, params) => {
    try {
        if (typeof route === 'function') return route(name, params)
    } catch (e) { }
    return '#'
}

// User data
const user = computed(() => page.props.auth?.user || {})

const userName = computed(() => user.value?.name || 'User')
const userEmail = computed(() => user.value?.email || 'user@example.com')
const userRole = computed(() => {
    const roles = user.value?.roles
    if (Array.isArray(roles)) return roles.map(r => r.name || r).join(', ')
    if (typeof roles === 'object' && roles?.name) return roles.name
    return 'User'
})
const userAvatar = computed(() => {
    if (user.value?.avatar) return user.value.avatar
    const name = userName.value || 'User'
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=5048e5&color=fff&size=128`
})

// Notifications (replace with real data later)
const notifications = ref([])
const unreadNotifications = computed(() => notifications.value.filter(n => !n.read).length)

const toggleMobileSidebar = () => emit('toggle-sidebar')
const toggleDesktopSidebar = () => emit('toggle-sidebar')

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value
    if (notificationsOpen.value) notificationsOpen.value = false
}

const toggleNotifications = () => {
    notificationsOpen.value = !notificationsOpen.value
    if (userMenuOpen.value) userMenuOpen.value = false
}

const closeUserMenu = () => (userMenuOpen.value = false)

const handleSearch = () => { }
const clearSearch = () => (searchQuery.value = '')

const markAllAsRead = () => {
    notifications.value = notifications.value.map(n => ({ ...n, read: true }))
}

const confirmLogout = () => {
    router.post('/logout', {}, {
        onFinish: () => {
            window.location.href = '/login'
        }
    })
}
const handleClickOutside = (event) => {
    if (
        userMenuOpen.value &&
        userMenuButton.value &&
        !userMenuButton.value.contains(event.target) &&
        userDropdown.value &&
        !userDropdown.value.contains(event.target)
    ) {
        userMenuOpen.value = false
    }
}

const handleEscapeKey = (e) => {
    if (e.key === 'Escape') {
        userMenuOpen.value = false
        notificationsOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleEscapeKey)
})
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease-out;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
