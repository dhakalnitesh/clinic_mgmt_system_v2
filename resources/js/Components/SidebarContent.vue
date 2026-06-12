<template>
    <div class="flex flex-col h-full">

        <!-- TOP: Dashboard (sticky) -->
        <div class="flex-shrink-0 px-2 pt-0.5   ">
            <button v-if="isMobile" @click="$emit('close')"
                class="lg:hidden flex size-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 mb-3 hover:bg-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-times"></i>
            </button>

            <a href="/dashboard" :class="linkClass('/dashboard')" @click="onLinkClick" :title="collapsed ? 'Dashboard' : undefined">
                <span class="icon-wrapper" :class="{ 'icon-active': pageUrl.startsWith('/dashboard') }">
                    <i class="fas fa-th-large"></i>
                </span>
                <span v-if="!collapsed">Dashboard</span>
            </a>

            <div v-if="!collapsed" class="mt-1 mb-2 border-t border-gray-200 dark:border-gray-700"></div>
        </div>

        <!-- MIDDLE: scrollable -->
        <nav class="flex-1 overflow-y-auto px-2 scrollbar-thin">
            <ul class="space-y-1">

                <!-- Clinical Section -->
                <li v-if="!collapsed" class="px-3 py-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Clinical</span>
                </li>

                <!-- Single-page modules (direct links) -->
                <li v-for="item in directLinks" :key="item.path">
                    <a :href="item.path" :class="linkClass(item.path)" @click="onLinkClick" :title="collapsed ? item.label : undefined">
                        <span class="icon-wrapper" :class="{ 'icon-active': pageUrl.startsWith(item.path) }">
                            <i :class="['fas', item.icon]"></i>
                        </span>
                        <span v-if="!collapsed">{{ item.label }}</span>
                    </a>
                </li>

                <li v-if="!collapsed" class="my-1 border-t border-gray-100 dark:border-gray-800"></li>

                <!-- Operations Section -->
                <li v-if="!collapsed" class="px-3 py-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Operations</span>
                </li>

                <!-- Multi-page modules (expandable) -->
                <li v-for="mod in expandableModules" :key="mod.module">
                    <button @click="toggleMenu(mod.module)" :class="groupBtnClass(mod.module)" :title="collapsed ? mod.module : undefined">
                        <span class="icon-wrapper" :class="{ 'icon-active': isActiveModule(mod) }">
                            <i :class="['fas', mod.icon]"></i>
                        </span>
                        <span v-if="!collapsed" class="flex-1 text-left text-sm font-medium">{{ mod.module }}</span>
                        <i v-if="!collapsed"
                            :class="openMenu === mod.module ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"
                            class="text-[10px] text-gray-400"></i>
                    </button>
                    <div v-if="openMenu === mod.module && !collapsed" class="ml-3 mt-0.5 space-y-0.5 border-l-2 border-teal-200 dark:border-teal-800 pl-3">
                        <a v-for="sub in mod.items" :key="sub.index_url" :href="sub.index_url"
                            :class="subLinkClass(sub.index_url)" @click="onLinkClick">
                            <span class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                {{ sub.index_name }}
                            </span>
                            <span v-if="sub.badge"
                                class="ml-auto inline-flex items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900/40 px-1.5 py-0.5 text-[10px] font-medium text-teal-700 dark:text-teal-300">
                                {{ sub.badge }}
                            </span>
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- BOTTOM: Reports (sticky) -->
        <div class="flex-shrink-0 px-2 pb-1">
            <div v-if="!collapsed" class="mt-1.5 mb-0.5 border-t border-gray-200 dark:border-gray-700"></div>

            <a href="/reports" :class="linkClass('/reports')" @click="onLinkClick" :title="collapsed ? 'Reports' : undefined">
                <span class="icon-wrapper" :class="{ 'icon-active': pageUrl.startsWith('/reports') }">
                    <i class="fas fa-chart-line"></i>
                </span>
                <span v-if="!collapsed">Reports</span>
            </a>
        </div>

    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    collapsed: Boolean,
    isMobile: Boolean,
})

const emit = defineEmits(['toggle', 'close', 'module-click'])

const page = usePage()
const pageUrl = page.url || '/'
const openMenu = ref(null)

const directLinks = [
    { label: 'Doctor Management', icon: 'fa-user-md', path: '/doctors' },
    { label: 'Patient Management', icon: 'fa-hospital-user', path: '/patients' },
    { label: 'Appointment', icon: 'fa-calendar-check', path: '/appointments' },
    { label: 'Visits', icon: 'fa-person-walking', path: '/visits' },
    { label: 'Consultations', icon: 'fa-comment-medical', path: '/consultations' },
    { label: 'Prescription', icon: 'fa-file-prescription', path: '/prescriptions' },
    { label: 'Follow-ups', icon: 'fa-calendar-alt', path: '/follow-ups' },
]

const expandableModules = [
    // {
    //     module: 'Pharmacy',
    //     icon: 'fa-capsules',
    //     items: [
    //         { index_url: '/pharmacy', index_name: 'Dashboard' },
    //         { index_url: '/pharmacy/medicines', index_name: 'Medicines' },
    //         { index_url: '/pharmacy/inventory', index_name: 'Inventory' },
    //         { index_url: '/pharmacy/sales', index_name: 'Sales' },
    //         { index_url: '/pharmacy/purchase-orders', index_name: 'Purchase Orders' },
    //         { index_url: '/pharmacy/grn', index_name: 'Goods Received' },
    //         { index_url: '/pharmacy/suppliers', index_name: 'Suppliers' },
    //         { index_url: '/pharmacy/prescriptions', index_name: 'Prescriptions' },
    //     ],
    // },
    {
        module: 'Laboratory',
        icon: 'fa-flask',
        items: [
            { index_url: '/laboratory/dashboard', index_name: 'Dashboard' },
            { index_url: '/laboratory/orders', index_name: 'Lab Orders' },
            { index_url: '/laboratory/results', index_name: 'Lab Results' },
            { index_url: '/laboratory/test-parameters', index_name: 'Test Parameters' },
        ],
    },
    {
        module: 'Billing',
        icon: 'fa-file-invoice-dollar',
        items: [
            { index_url: '/billing/invoices', index_name: 'Invoices' },
            { index_url: '/billing/payments', index_name: 'Payments' },
            { index_url: '/dues', index_name: 'Due Management' },
        ],
    },
]

const isActiveModule = (mod) =>
    mod.items.some(item => pageUrl.startsWith(item.index_url))

watch(() => page.url, (url) => {
    for (const mod of expandableModules) {
        if (isActiveModule(mod)) {
            openMenu.value = mod.module
            return
        }
    }
}, { immediate: true })

const toggleMenu = (name) => {
    if (props.collapsed) {
        emit('toggle')
        openMenu.value = name
        return
    }
    openMenu.value = openMenu.value === name ? null : name
}

const onLinkClick = () => {
    if (props.isMobile) emit('close')
}

const baseLink = 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-200 cursor-pointer'

const linkClass = (path) => [
    baseLink,
    'w-full',
    pageUrl.startsWith(path)
        ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-semibold'
        : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700/50'
]

const groupBtnClass = (name) => [
    baseLink,
    'w-full',
    openMenu.value === name
        ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-semibold'
        : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700/50'
]

const subLinkClass = (path) => [
    'flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-all duration-200',
    pageUrl.startsWith(path)
        ? 'text-teal-700 dark:text-teal-300 bg-teal-100 dark:bg-teal-900/30 font-semibold'
        : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700/50'
]
</script>

<style scoped>
.icon-wrapper {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    flex-shrink: 0;
    font-size: 13px;
    transition: all 0.2s ease;
}
.icon-wrapper.icon-active,
a:hover .icon-wrapper,
button:hover .icon-wrapper {
    background: rgba(13, 148, 136, 0.18);
}

.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.15);
    border-radius: 4px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.25);
}
.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
}
.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.25);
}

@keyframes ping {
    75%, 100% { transform: scale(2); opacity: 0; }
}
.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
