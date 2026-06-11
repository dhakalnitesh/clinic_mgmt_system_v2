<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
    parameters: Object,
    filters: Object,
})

const filterForm = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    from_date: props.filters.from_date ?? '',
    to_date: props.filters.to_date ?? '',
    per_page: props.filters.per_page ?? 15,
})

const filter = () => {
    router.get(route('laboratory.test-parameters.index'), filterForm, {
        preserveState: true,
        replace: true,
    })
}

const resetFilters = () => {
    filterForm.search = ''
    filterForm.status = ''
    filterForm.from_date = ''
    filterForm.to_date = ''
    filterForm.per_page = 15

    filter()
}
</script>

<template>

    <Head title="Lab Test Parameters" />

    <AuthenticatedLayout>

        <div class="space-y-6 p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Lab Test Parameters
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage laboratory test parameters and reference ranges.
                    </p>
                </div>

                <Link :href="route('laboratory.test-parameters.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 font-semibold text-white shadow hover:bg-teal-700">

                <i class="fas fa-plus"></i>
                Add Parameter
                </Link>

            </div>
    <!-- Filters -->
            <div class="rounded-2xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-5 shadow-sm">

                <div class="grid gap-4 md:grid-cols-6">

                    <!-- Search -->
                    <div class="md:col-span-2">

                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Search
                        </label>

                        <input v-model="filterForm.search" type="text"
                            placeholder="Parameter or Lab Test..."
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />

                    </div>

                    <!-- From Date -->
                    <div>

                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            From Date
                        </label>

                        <input v-model="filterForm.from_date" type="date"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />

                    </div>

                    <!-- To Date -->
                    <div>

                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            To Date
                        </label>

                        <input v-model="filterForm.to_date" type="date"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />

                    </div>

                    <!-- Status -->
                    <div>

                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status
                        </label>

                        <select v-model="filterForm.status"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">

                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>

                        </select>

                    </div>

                    <!-- Per Page -->
                    <div>

                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Rows
                        </label>

                        <select v-model="filterForm.per_page"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">


                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>

                        </select>

                    </div>

                </div>

                <div class="mt-5 flex gap-3">

                    <button @click="filter"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 font-medium text-white hover:bg-teal-700">

                        <i class="fas fa-filter"></i>
                        Filter

                    </button>

                    <button @click="resetFilters"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2 font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">

                        <i class="fas fa-rotate-left"></i>
                        Reset

                    </button>

                </div>

            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Parameter Name
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Lab Test
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Unit
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Reference Range
                                </th>

                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Display Order
                                </th>

                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Status
                                </th>

                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">

                            <tr v-for="parameter in parameters.data" :key="parameter.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50">

                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">
                                    {{ parameter.name }}
                                </td>

                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ parameter.lab_test?.name }}
                                </td>

                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ parameter.unit ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ parameter.reference_range ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                    {{ parameter.display_order }}
                                </td>

                                <td class="px-6 py-4 text-center">

                                    <span v-if="parameter.is_active"
                                        class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">

                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active

                                    </span>

                                    <span v-else
                                        class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">

                                        <i class="fas fa-times-circle mr-1"></i>
                                        Inactive

                                    </span>

                                </td>

                                <td class="px-6 py-4">

                                    <div class="flex justify-end gap-2">

                                        <Link :href="route('laboratory.test-parameters.edit', parameter.id)"
                                            class="rounded-lg bg-teal-50 p-2 text-teal-600 hover:bg-teal-100 dark:bg-teal-900/20 dark:text-teal-400 dark:hover:bg-teal-900/40">

                                        <i class="fas fa-pen"></i>
                                        </Link>

                                    </div>

                                </td>

                            </tr>

                            <tr v-if="parameters.data.length === 0">

                                <td colspan="7" class="px-6 py-16 text-center text-gray-500">

                                    <div
                                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">

                                        <i class="fas fa-flask text-2xl text-gray-400"></i>

                                    </div>

                                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                        No Parameters Found
                                    </h3>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-6 py-4">

                    <div class="text-sm text-gray-600 dark:text-gray-400">

                        Showing
                        {{ parameters.from }}
                        to
                        {{ parameters.to }}
                        of
                        {{ parameters.total }}
                        entries

                    </div>

                    <div class="flex gap-1">

                        <Link v-for="link in parameters.links" :key="link.label" :href="link.url ?? '#'"
                            v-html="link.label" :class="[
                                'rounded-lg px-3 py-2 text-sm',
                                link.active
                                    ? 'bg-teal-600 text-white'
                                    : 'bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600',
                                !link.url && 'pointer-events-none opacity-50'
                            ]" />

                    </div>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>