<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Doughnut, Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title)

const props = defineProps({
    stats: Object,
    statusDistribution: Array,
    weeklyData: Array,
    pendingOrders: Array,
    processingOrders: Array,
    recentOrders: Array,
})

const statCards = computed(() => [
    {
        label: 'Total Orders',
        value: props.stats?.total ?? 0,
        icon: 'fa-flask',
        bgClass: 'bg-blue-50 dark:bg-blue-900/20',
        colorClass: 'text-blue-600 dark:text-blue-400',
        route: '/laboratory/orders',
    },
    {
        label: 'Pending Collection',
        value: props.stats?.ordered ?? 0,
        icon: 'fa-clock',
        bgClass: 'bg-amber-50 dark:bg-amber-900/20',
        colorClass: 'text-amber-600 dark:text-amber-400',
        route: '/laboratory/orders?status=ordered',
    },
    {
        label: 'In Processing',
        value: props.stats?.processing ?? 0,
        icon: 'fa-cogs',
        bgClass: 'bg-purple-50 dark:bg-purple-900/20',
        colorClass: 'text-purple-600 dark:text-purple-400',
        route: '/laboratory/orders?status=processing',
    },
    {
        label: 'Completed Today',
        value: props.stats?.completedToday ?? 0,
        icon: 'fa-check-circle',
        bgClass: 'bg-emerald-50 dark:bg-emerald-900/20',
        colorClass: 'text-emerald-600 dark:text-emerald-400',
        route: '/laboratory/orders?status=completed',
    },
    {
        label: "Today's Orders",
        value: props.stats?.orderedToday ?? 0,
        icon: 'fa-plus-circle',
        bgClass: 'bg-cyan-50 dark:bg-cyan-900/20',
        colorClass: 'text-cyan-600 dark:text-cyan-400',
        route: '/laboratory/orders',
    },
])

const donutData = computed(() => ({
    labels: props.statusDistribution?.map(s => s.status.replace('_', ' ')) ?? [],
    datasets: [{
        data: props.statusDistribution?.map(s => s.count) ?? [],
        backgroundColor: ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#ef4444'],
        borderWidth: 2,
        borderColor: '#fff',
    }],
}))

const donutOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: 'bottom',
            labels: { padding: 14, usePointStyle: true, font: { size: 11 } },
        },
    },
    cutout: '65%',
}

const barData = computed(() => ({
    labels: props.weeklyData?.map(d => d.nepali_date ?? d.label) ?? [],
    datasets: [
        {
            label: 'New Orders',
            backgroundColor: 'rgba(99, 102, 241, 0.7)',
            hoverBackgroundColor: 'rgba(99, 102, 241, 0.9)',
            borderRadius: 6,
            borderSkipped: false,
            data: props.weeklyData?.map(d => d.new) ?? [],
        },
        {
            label: 'Completed',
            backgroundColor: 'rgba(16, 185, 129, 0.7)',
            hoverBackgroundColor: 'rgba(16, 185, 129, 0.9)',
            borderRadius: 6,
            borderSkipped: false,
            data: props.weeklyData?.map(d => d.completed) ?? [],
        },
    ],
}))

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            align: 'end',
            labels: { usePointStyle: true, padding: 16, font: { size: 12 } },
        },
        tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 13, weight: '600' },
            bodyFont: { size: 12 },
            padding: 12,
            cornerRadius: 8,
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 10 }, color: '#94a3b8', maxRotation: 0 },
        },
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1, precision: 0, font: { size: 11 }, color: '#94a3b8' },
            grid: { color: 'rgba(148, 163, 184, 0.15)', drawBorder: false },
        },
    },
}

const getStatusClass = (status) => {
    switch (status) {
        case 'ordered': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
        case 'sample_collected': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
        case 'processing': return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
        case 'completed': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
        case 'cancelled': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
        default: return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    }
}
</script>

<template>
    <Head title="Laboratory Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl space-y-6 p-6">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-gray-100">
                            Laboratory Dashboard
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Real-time lab orders, samples & results overview
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="rounded-2xl border bg-white px-5 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Today</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                {{ new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) }}
                            </div>
                        </div>
                        <div class="rounded-2xl bg-teal-600 px-5 py-3 text-white shadow-sm">
                            <div class="text-xs font-semibold text-teal-100">Nepali Date</div>
                            <div class="text-sm font-bold">{{ weeklyData?.[weeklyData.length - 1]?.nepali_date ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
                    <div
                        v-for="card in statCards"
                        :key="card.label"
                        class="flex cursor-pointer items-center gap-4 rounded-2xl border bg-white p-5 shadow-sm transition-all hover:scale-[1.02] hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex items-center justify-center rounded-xl p-3 text-2xl" :class="card.bgClass">
                            <i :class="['fas', card.icon, card.colorClass]"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ card.label }}
                            </div>
                            <div class="text-2xl font-black text-gray-800 dark:text-gray-100">
                                {{ card.value }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">

                    <div class="space-y-6 lg:col-span-2">

                        <div class="rounded-2xl border bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                                <i class="fas fa-chart-bar text-indigo-600"></i>
                                Weekly Lab Activity
                            </h3>
                            <div class="relative" style="height: 220px">
                                <Bar v-if="weeklyData?.length" :data="barData" :options="barOptions" />
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="rounded-2xl border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                                    <h3 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                                        <i class="fas fa-hourglass-half text-amber-500"></i>
                                        Pending Collection
                                    </h3>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                        {{ stats?.ordered ?? 0 }}
                                    </span>
                                </div>
                                <div v-if="pendingOrders.length" class="divide-y dark:divide-gray-700">
                                    <div
                                        v-for="order in pendingOrders"
                                        :key="order.id"
                                        class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ order.patient?.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ order.order_number }}
                                            </div>
                                        </div>
                                        <Link
                                            :href="route('laboratory.orders.show', order.id)"
                                            class="ml-3 shrink-0 rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50"
                                        >
                                            Open
                                        </Link>
                                    </div>
                                </div>
                                <div v-else class="p-8 text-center text-sm text-gray-400">
                                    No pending orders.
                                </div>
                            </div>

                            <div class="rounded-2xl border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                                    <h3 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                                        <i class="fas fa-sync-alt text-purple-500"></i>
                                        In Processing
                                    </h3>
                                    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ stats?.processing ?? 0 }}
                                    </span>
                                </div>
                                <div v-if="processingOrders.length" class="divide-y dark:divide-gray-700">
                                    <div
                                        v-for="order in processingOrders"
                                        :key="order.id"
                                        class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ order.patient?.name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ order.order_number }}
                                            </div>
                                        </div>
                                        <Link
                                            :href="route('laboratory.orders.show', order.id)"
                                            class="ml-3 shrink-0 rounded-lg bg-purple-100 px-3 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:hover:bg-purple-900/50"
                                        >
                                            View
                                        </Link>
                                    </div>
                                </div>
                                <div v-else class="p-8 text-center text-sm text-gray-400">
                                    Nothing in processing.
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b px-5 py-4 dark:border-gray-700">
                                <h3 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                                    <i class="fas fa-list text-gray-500"></i>
                                    Recent Lab Orders
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Order</th>
                                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Patient</th>
                                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Doctor</th>
                                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Status</th>
                                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y dark:divide-gray-700">
                                        <tr
                                            v-for="order in recentOrders"
                                            :key="order.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ order.order_number }}
                                            </td>
                                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                                {{ order.patient?.name }}
                                            </td>
                                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                                {{ order.doctor?.name }}
                                            </td>
                                            <td class="px-5 py-4">
                                                <span
                                                    class="inline-block rounded-full px-2.5 py-1 text-xs font-medium"
                                                    :class="getStatusClass(order.status)"
                                                >
                                                    {{ order.status.replace('_', ' ') }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <Link
                                                    :href="route('laboratory.orders.show', order.id)"
                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    View
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="space-y-6">

                        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-3 text-center text-sm font-bold text-gray-800 dark:text-gray-100">
                                Status Distribution
                            </h3>
                            <Doughnut v-if="statusDistribution?.length" :data="donutData" :options="donutOptions" class="mx-auto max-h-52" />
                        </div>

                        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                                <i class="fas fa-bolt text-indigo-600"></i>
                                Quick Actions
                            </h3>
                            <div class="space-y-2.5">
                                <Link
                                    :href="route('laboratory.orders.index')"
                                    class="flex items-center gap-3 rounded-xl bg-indigo-50 p-3 text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/30"
                                >
                                    <i class="fas fa-list w-5 text-center"></i>
                                    All Orders
                                </Link>
                                <Link
                                    :href="route('laboratory.results.index')"
                                    class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-700 transition-colors hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                                >
                                    <i class="fas fa-file-medical-alt w-5 text-center"></i>
                                    Lab Results
                                </Link>
                                <Link
                                    :href="route('laboratory.test-parameters.index')"
                                    class="flex items-center gap-3 rounded-xl bg-purple-50 p-3 text-sm font-semibold text-purple-700 transition-colors hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:hover:bg-purple-900/30"
                                >
                                    <i class="fas fa-sliders-h w-5 text-center"></i>
                                    Test Parameters
                                </Link>
                            </div>
                        </div>

                        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending</div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">Sample Collection</div>
                                </div>
                                <div class="text-3xl font-black text-amber-600 dark:text-amber-400">
                                    {{ stats?.ordered ?? 0 }}
                                </div>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                <div
                                    class="h-full rounded-full bg-amber-500 transition-all"
                                    :style="{ width: stats?.total ? Math.round((stats.ordered / stats.total) * 100) + '%' : '0%' }"
                                ></div>
                            </div>
                            <div class="mt-1 text-right text-xs text-gray-400">
                                {{ stats?.total ? Math.round((stats.ordered / stats.total) * 100) : 0 }}% of total
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
