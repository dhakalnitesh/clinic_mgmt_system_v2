<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
    orders: Object,
    filters: Object,
})

const filterForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    from_date: props.filters.from_date || '',
    to_date: props.filters.to_date || '',
    per_page: props.filters.per_page || 10,
})

const applyFilters = () => {
    router.get(route('laboratory.orders.index'), filterForm, {
        preserveState: true,
        replace: true,
    })
}

const resetFilters = () => {
    router.get(route('laboratory.orders.index'))
}

const statusClass = (status) => {
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
<Head title="Laboratory Orders" />

<AuthenticatedLayout>

<div class="p-6">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Laboratory Orders
        </h1>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage laboratory requests, samples and reports.
        </p>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-2xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-5 shadow-sm">

        <div class="grid gap-4 md:grid-cols-6">

            <!-- Search -->
            <div class="md:col-span-2">
                    <input
                        v-model="filterForm.search"
                        type="text"
                        placeholder="Order No, Patient, UHID, Doctor"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    >
                </div>

                <!-- Status -->
                <div>
                    <select
                        v-model="filterForm.status"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    >
                    <option value="">All Status</option>
                    <option value="ordered">Ordered</option>
                    <option value="sample_collected">Sample Collected</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Per Page -->
            <div>
                <select
                    v-model="filterForm.per_page"
                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                >
                    <option :value="10">10 Rows</option>
                    <option :value="25">25 Rows</option>
                    <option :value="50">50 Rows</option>
                    <option :value="100">100 Rows</option>
                </select>
            </div>

            <!-- From Date -->
            <div>
                <input
                    v-model="filterForm.from_date"
                    type="date"
                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                >
            </div>

            <!-- To Date -->
            <div>
                <input
                    v-model="filterForm.to_date"
                    type="date"
                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                >
            </div>

        </div>

        <div class="mt-4 flex gap-3">

            <button
                @click="applyFilters"
                class="rounded-xl bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
            >
                Apply Filters
            </button>

            <button
                @click="resetFilters"
                class="rounded-xl border px-4 py-2 hover:bg-gray-50"
            >
                Reset
            </button>

        </div>

    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-gray-50 dark:bg-gray-700">

                    <tr>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Order No
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Patient
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Doctor
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Tests
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Status
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Date
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <tr
                        v-for="order in orders.data"
                        :key="order.id"
                        class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    >

                        <!-- Order -->
                        <td class="px-6 py-4">

                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ order.order_number }}
                            </div>

                        </td>

                        <!-- Patient -->
                        <td class="px-6 py-4">

                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ order.patient?.name }}
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                UHID:
                                {{ order.patient?.uhid }}
                            </div>

                        </td>

                        <!-- Doctor -->
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ order.doctor?.name }}
                        </td>

                        <!-- Tests -->
                        <td class="px-6 py-4">

                            <span
                                class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700"
                            >
                                {{ order.items_count }} Tests
                            </span>

                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">

                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="statusClass(order.status)"
                            >
                                {{ order.status.replaceAll('_', ' ') }}
                            </span>

                        </td>

                        <!-- Date -->
                        <td class="px-6 py-4">

                            {{
                                new Date(
                                    order.created_at
                                ).toLocaleDateString()
                            }}

                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">

                            <Link
                                :href="route('laboratory.orders.show', order.id)"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
                            >
                                Open
                            </Link>

                        </td>

                    </tr>

                    <tr v-if="orders.data.length === 0">

                        <td
                            colspan="7"
                            class="py-12 text-center text-gray-500 dark:text-gray-400"
                        >
                            No laboratory orders found.
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-700 px-6 py-4">

            <div class="text-sm text-gray-500 dark:text-gray-400">

                Showing
                {{ orders.from }}
                to
                {{ orders.to }}
                of
                {{ orders.total }}
                orders

            </div>

            <div class="flex gap-2">

                <Link
                    v-for="link in orders.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="rounded-lg px-3 py-2 text-sm"
                    :class="[
                        link.active
                            ? 'bg-indigo-600 text-white'
                            : 'border bg-white'
                    ]"
                />

            </div>

        </div>

    </div>

</div>

</AuthenticatedLayout>
</template>