<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import NepaliDatepicker from '@/Components/NepaliDatepicker.vue'
import { ref, watch } from 'vue'

const props = defineProps({
    orders: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const fromDate = ref(props.filters?.from_date ?? '')
const toDate = ref(props.filters?.to_date ?? '')
const perPage = ref(props.filters?.per_page ?? 10)

const searchTimeout = ref(null)
watch(search, () => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => applyFilters(), 400)
})

const applyFilters = () => {
    router.get(
        route('laboratory.results.index'),
        {
            search: search.value || '',
            from_date: fromDate.value || '',
            to_date: toDate.value || '',
            per_page: perPage.value,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    )
}

const resetFilters = () => {
    search.value = ''
    fromDate.value = ''
    toDate.value = ''
    perPage.value = 10
    applyFilters()
}

const getStatusClass = (status) => {
    switch (status) {
        case 'completed': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
        default: return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    }
}
</script>

<template>
    <Head title="Lab Results" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 p-6 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Lab Results</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        View and export completed lab test results.
                    </p>
                </div>

                <div class="mb-6 rounded-2xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="min-w-0 flex-1">
                            <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Order #, patient, doctor..."
                                class="w-full rounded-xl border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">From (BS)</label>
                            <NepaliDatepicker v-model="fromDate" placeholder="From (BS)" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">To (BS)</label>
                            <NepaliDatepicker v-model="toDate" placeholder="To (BS)" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Per page</label>
                            <select
                                v-model="perPage"
                                @change="applyFilters"
                                class="rounded-xl border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="resetFilters"
                                class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                            >
                                Reset
                            </button>
                            <button
                                @click="applyFilters"
                                class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                Filter
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="orders?.data?.length" class="space-y-4">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        class="rounded-2xl border bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b px-6 py-4 dark:border-gray-700">
                            <div>
                                <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ order.order_number }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ order.patient?.name }} &middot; {{ order.patient?.uhid }}
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-medium"
                                    :class="getStatusClass(order.status)"
                                >
                                    {{ order.status.replace('_', ' ') }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    BS: {{ order.created_at_bs || '-' }}
                                </span>
                                <Link
                                    :href="route('laboratory.orders.show', order.id)"
                                    class="rounded-xl border px-4 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    View Order
                                </Link>
                                <Link
                                    :href="route('laboratory.orders.results.print', order.id)"
                                    class="rounded-xl bg-indigo-50 px-4 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/30"
                                >
                                    <i class="fas fa-print mr-1"></i> Print
                                </Link>
                                <Link
                                    :href="route('laboratory.orders.results.export.csv', order.id)"
                                    class="rounded-xl bg-emerald-50 px-4 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                                >
                                    <i class="fas fa-file-csv mr-1"></i> CSV
                                </Link>
                                <Link
                                    :href="route('laboratory.orders.results.export.pdf', order.id)"
                                    class="rounded-xl bg-red-50 px-4 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                >
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </Link>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <div class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Doctor: {{ order.doctor?.name }}</div>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="rounded-xl bg-gray-50 p-3 dark:bg-gray-700/50"
                                >
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        {{ item.lab_test?.name }}
                                    </div>
                                    <div v-if="item.results?.length" class="mt-2 space-y-1">
                                        <div
                                            v-for="result in item.results"
                                            :key="result.id"
                                            class="flex justify-between text-xs"
                                        >
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ result.parameter?.name ?? '—' }}
                                            </span>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                                {{ result.result_value ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-else class="mt-1 text-xs text-gray-400 italic">
                                        No results entered yet.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl border bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <i class="fas fa-file-medical-alt mb-4 text-5xl text-gray-300 dark:text-gray-600"></i>
                    <p class="text-gray-500 dark:text-gray-400">No lab results found.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">
                        Complete lab orders will appear here with their results.
                    </p>
                </div>

                <div v-if="orders?.links" class="mt-6">
                    <div class="flex flex-wrap justify-center gap-2">
                        <template v-for="(link, i) in orders.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded-xl px-4 py-2 text-sm"
                                :class="link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'border bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'"
                                v-html="link.label"
                            />
                            <span v-else class="rounded-xl bg-gray-100 px-4 py-2 text-sm text-gray-400 dark:bg-gray-800 dark:text-gray-600" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
