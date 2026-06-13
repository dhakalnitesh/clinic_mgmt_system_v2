<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import NepaliDatepicker from '@/Components/NepaliDatepicker.vue'
import Pagination from '@/Components/Pagination.vue'
import CreateModal from './CreateModal.vue'

const props = defineProps({
    parameters: Object,
    labTests: Array,
    filters: Object,
})

const showCreateModal = ref(false)
const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')
const fromDate = ref(props.filters?.from_date ?? '')
const toDate = ref(props.filters?.to_date ?? '')
const perPage = ref(props.filters?.per_page ?? 15)

const applyFilters = () => {
    router.get(route('laboratory.test-parameters.index'), {
        search: search.value || '',
        status: status.value || '',
        from_date: fromDate.value || '',
        to_date: toDate.value || '',
        per_page: perPage.value,
    }, { preserveState: true, replace: true })
}

const resetFilters = () => {
    search.value = ''
    status.value = ''
    fromDate.value = ''
    toDate.value = ''
    perPage.value = 15
    applyFilters()
}
</script>

<template>
    <Head title="Lab Test Parameters" />
    <AuthenticatedLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Lab Test Parameters</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage laboratory test parameters and reference ranges.</p>
                </div>
                <button @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
                    <i class="fas fa-plus"></i>
                    Add Parameter
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-48">
                    <label class="text-xs font-medium text-slate-500 mb-1 block">Search</label>
                    <input v-model="search" @input="applyFilters" type="text"
                           placeholder="Parameter or Lab Test..."
                           class="form-input" />
                </div>
                <div class="w-44">
                    <label class="text-xs font-medium text-slate-500 mb-1 block">From Date (BS)</label>
                    <NepaliDatepicker v-model="fromDate" placeholder="Start date" />
                </div>
                <div class="w-44">
                    <label class="text-xs font-medium text-slate-500 mb-1 block">To Date (BS)</label>
                    <NepaliDatepicker v-model="toDate" placeholder="End date" />
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-500 mb-1 block">Status</label>
                    <select v-model="status" @change="applyFilters" class="form-select text-sm">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="w-24">
                    <label class="text-xs font-medium text-slate-500 mb-1 block">Rows</label>
                    <select v-model="perPage" @change="applyFilters" class="form-select text-sm">
                        <option :value="10">10</option>
                        <option :value="15">15</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>
                <button @click="resetFilters"
                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition self-end">
                    Reset
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Parameter</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Lab Test</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Ref. Range</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Order</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in parameters.data" :key="p.id" class="hover:bg-slate-50/60">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ p.name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ p.lab_test?.name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ p.unit ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 font-mono text-xs">{{ p.reference_range ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-slate-600">{{ p.display_order }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span v-if="p.is_active"
                                          class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                        Active
                                    </span>
                                    <span v-else
                                          class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-600 ring-1 ring-red-200">
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('laboratory.test-parameters.edit', p.id)"
                                          class="inline-flex items-center gap-1 rounded-lg bg-teal-50 px-3 py-1.5 text-sm font-medium text-teal-700 hover:bg-teal-100 transition">
                                        <i class="fas fa-pen text-xs"></i> Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!parameters.data.length">
                                <td colspan="7" class="py-16 text-center text-slate-400">
                                    <i class="fas fa-flask text-3xl mb-2 opacity-40"></i>
                                    <p class="text-sm font-medium">No parameters found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Showing {{ parameters.from ?? 0 }}–{{ parameters.to ?? 0 }} of {{ parameters.total }}
                    </p>
                    <Pagination :links="parameters.links" />
                </div>
            </div>
        </div>
        <CreateModal
            v-if="showCreateModal"
            :lab-tests="labTests"
            @close="showCreateModal = false"
            @success="showCreateModal = false"
        />
    </AuthenticatedLayout>
</template>
