<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    stats: Object,

    pendingOrders: {
        type: Array,
        default: () => [],
    },

    processingOrders: {
        type: Array,
        default: () => [],
    },

    recentOrders: {
        type: Array,
        default: () => [],
    },
})

const dashboardStats = computed(() => [
    {
        label: 'Pending Orders',
        value: props.stats?.ordered ?? 0,
        color: 'text-amber-600',
    },
    {
        label: 'Sample Collected',
        value: props.stats?.sample_collected ?? 0,
        color: 'text-blue-600',
    },
    {
        label: 'Processing',
        value: props.stats?.processing ?? 0,
        color: 'text-purple-600',
    },
    {
        label: 'Completed',
        value: props.stats?.completed ?? 0,
        color: 'text-emerald-600',
    },
])

const getStatusClass = (status) => {
    switch (status) {
        case 'ordered':
            return 'bg-amber-100 text-amber-700'

        case 'sample_collected':
            return 'bg-blue-100 text-blue-700'

        case 'processing':
            return 'bg-purple-100 text-purple-700'

        case 'completed':
            return 'bg-emerald-100 text-emerald-700'

        case 'cancelled':
            return 'bg-red-100 text-red-700'

        default:
            return 'bg-gray-100 text-gray-700'
    }
}
</script>

<template>

<Head title="Laboratory Dashboard" />

<AuthenticatedLayout>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">

    <div class="mx-auto max-w-7xl space-y-6 p-6">

    <!-- HEADER -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Laboratory Dashboard
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage laboratory orders, sample collection and reporting.
            </p>
        </div>

        <div class="text-right">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Today
            </div>

            <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                {{ new Date().toDateString() }}
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">

        <div
            v-for="item in dashboardStats"
            :key="item.label"
            class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-5 shadow-sm"
        >
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ item.label }}
            </div>

            <div
                class="mt-2 text-3xl font-bold"
                :class="item.color"
            >
                {{ item.value }}
            </div>
        </div>

    </div>

    <!-- MAIN CONTENT -->
    <div class="grid gap-6 lg:grid-cols-3">

        <!-- LEFT -->
        <div class="space-y-6 lg:col-span-2">

            <!-- PENDING ORDERS -->
            <div class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm">

                <div class="flex items-center justify-between border-b dark:border-gray-700 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Pending Sample Collection
                    </h2>

                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Awaiting action
                    </span>
                </div>

                <div
                    v-if="pendingOrders.length"
                    class="divide-y dark:divide-gray-700"
                >
                    <div
                        v-for="order in pendingOrders"
                        :key="order.id"
                        class="flex flex-col gap-4 p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <div class="font-bold text-gray-900 dark:text-gray-100">
                                {{ order.patient?.name }}
                            </div>

                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ order.order_number }}
                            </div>
                        </div>

                        <span
                            class="rounded-full bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-xs text-amber-700 dark:text-amber-300"
                        >
                            Ordered
                        </span>

                        <Link
                            :href="route('laboratory.orders.show', order.id)"
                            class="inline-flex items-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Open Order
                        </Link>
                    </div>
                </div>

                <div
                    v-else
                    class="p-10 text-center text-gray-500 dark:text-gray-400"
                >
                    No pending orders.
                </div>

            </div>

            <!-- RECENT ORDERS -->
            <div class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm">

                <div class="border-b dark:border-gray-700 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Recent Lab Orders
                    </h2>
                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Order
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Patient
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Doctor
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Status
                                </th>

                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y dark:divide-gray-700">

                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ order.order_number }}
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ order.patient?.name }}
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ order.doctor?.name }}
                                </td>

                                <td class="px-6 py-4">

                                    <span
                                        class="rounded-full px-3 py-1 text-xs"
                                        :class="getStatusClass(order.status)"
                                    >
                                        {{ order.status }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 text-right">

                                    <Link
                                        :href="route('laboratory.orders.show', order.id)"
                                        class="font-semibold text-indigo-600 hover:underline"
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

        <!-- RIGHT SIDEBAR -->
        <div class="space-y-6">

            <div class="rounded-3xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-5">
                <div class="text-sm font-semibold text-amber-700 dark:text-amber-400">
                    Pending Collection
                </div>

                <div class="mt-2 text-3xl font-bold text-amber-800 dark:text-amber-300">
                    {{ stats?.ordered ?? 0 }}
                </div>
            </div>

            <div class="rounded-3xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 p-5">
                <div class="text-sm font-semibold text-purple-700 dark:text-purple-400">
                    Processing
                </div>

                <div class="mt-2 text-3xl font-bold text-purple-800 dark:text-purple-300">
                    {{ stats?.processing ?? 0 }}
                </div>
            </div>

            <div class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-5 shadow-sm">

                <div class="font-bold text-gray-900 dark:text-gray-100">
                    Quick Actions
                </div>

                <div class="mt-4 space-y-3">

                    <!-- <Link
                        :href="route('laboratory.orders.index')"
                        class="block text-sm font-medium text-indigo-600 hover:underline"
                    >
                        View All Orders
                    </Link> -->

                    <!-- <Link
                        :href="route('lab-tests.index')"
                        class="block text-sm font-medium text-indigo-600 hover:underline"
                    >
                        Manage Lab Tests
                    </Link> -->

                    <!-- <Link
                        :href="route('lab-test-categories.index')"
                        class="block text-sm font-medium text-indigo-600 hover:underline"
                    >
                        Test Categories
                    </Link> -->

                </div>

            </div>

        </div>

    </div>

</div>

</div>

</AuthenticatedLayout>

</template>