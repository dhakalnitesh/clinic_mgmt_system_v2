<script setup>
import { ref } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

import CreateModal from './CreateModal.vue'
import EditModal from './EditModal.vue'
import DeleteModal from './DeleteModal.vue'
import ShowModal from './ShowModal.vue'

const props = defineProps({
    patients: Object,
    provinces: {
        type: Array,
        default: () => []
    },
    districts: {
        type: Array,
        default: () => []
    },
    municipals: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    can: {
        type: Object,
        default: () => ({})
    }
})

const refreshData = () => {
    router.reload({
        only: ['patients'],
    })
}

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
*/

const canCreate = props.can?.create ?? true
const canEdit = props.can?.edit ?? true
const canDelete = props.can?.delete ?? true

/*
|--------------------------------------------------------------------------
| Modals
|--------------------------------------------------------------------------
*/

const showCreateModal = ref(false)
const selectedItem = ref(null)
const deleteItem = ref(null)
const showItem = ref(null)

const openCreateModal = () => {
    showCreateModal.value = true
}

const openEditModal = (patient) => {
    selectedItem.value = patient
}

const openDeleteModal = (patient) => {
    deleteItem.value = patient
}

const openShowModal = (patient) => {
    showItem.value = patient
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Patients" />

        <div class="p-6 space-y-6">

            <!-- Header -->
            <div class="flex justify-between items-center">

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-hospital-user text-indigo-600"></i>
                        Patients
                    </h1>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Manage clinic patients and records
                    </p>
                </div>

                <button v-if="canCreate" @click="openCreateModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow">
                    + Create
                </button>

            </div>

            <FilterBar
                route-name="patients.index"
                :filters="filters"
                search-placeholder="Patient Name, Phone, Gender..."
            />

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">

                            <tr>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    SN
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    Name
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    Age
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    Gender
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    Phone
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    Address
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    Registered (BS)
                                </th>

                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                            <tr v-for="(patient, index) in patients.data" :key="patient.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">

                                <td class="px-6 py-4 dark:text-gray-300">
                                    {{
                                        ((patients.current_page - 1) * patients.per_page)
                                        + index + 1
                                    }}
                                </td>

                                <td class="px-6 py-4 font-medium dark:text-gray-100">
                                    {{ patient.name }}
                                </td>

                                <td class="px-6 py-4 dark:text-gray-300">
                                    {{ patient.age || '-' }}
                                </td>

                                <td class="px-6 py-4 dark:text-gray-300">
                                    {{ patient.gender || '-' }}
                                </td>

                                <td class="px-6 py-4 dark:text-gray-300">
                                    {{ patient.phone || '-' }}
                                </td>

                                <td class="px-6 py-4 dark:text-gray-300">
                                    {{ patient.address1 || '-' }}
                                </td>

                                <td class="px-6 py-4 dark:text-gray-300">
                                    {{ patient.created_at_bs || '-' }}
                                </td>

                                <td class="px-6 py-4">

                                    <div class="flex justify-end gap-3">

                                        <Link :href="route('patients.show', patient.id)"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                            <i class="fas fa-eye"></i>
                                        </Link>

                                        <button v-if="canEdit" @click="openEditModal(patient)"
                                            class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button v-if="canDelete" @click="openDeleteModal(patient)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                            <tr v-if="!patients.data.length">

                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No patients found.
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <div v-if="patients?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t dark:border-gray-700 px-6 py-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-medium dark:text-gray-200">{{ patients.from || 0 }}</span>
                        to <span class="font-medium dark:text-gray-200">{{ patients.to || 0 }}</span>
                        of <span class="font-medium dark:text-gray-200">{{ patients.total }}</span> results
                    </div>
                    <Pagination :links="patients.links" />
                </div>

            </div>

        </div>

        <!-- Modals -->
        <CreateModal v-if="showCreateModal" :provinces="provinces" :districts="districts" :municipals="municipals" @close="showCreateModal = false" @success="refreshData" />

        <ShowModal v-if="showItem" :patient="showItem" @close="showItem = null" @success="refreshData" />

        <EditModal v-if="selectedItem" :patient="selectedItem" :provinces="provinces" :districts="districts" :municipals="municipals" @close="selectedItem = null" @success="refreshData" />

        <DeleteModal v-if="deleteItem" :item="deleteItem" @close="deleteItem = null" @success="refreshData" />

    </AuthenticatedLayout>
</template>