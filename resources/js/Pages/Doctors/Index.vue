<template>
  <AuthenticatedLayout>
  <Head title="Doctors" />
  <div class="p-6 space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
          <i class="fas fa-user-md text-indigo-600"></i>
          Doctors
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Manage doctors and their schedules</p>
      </div>

      <button v-if="canCreate" @click="openCreateModal"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow">
        + Create
      </button>
    </div>

  <FilterBar
      route-name="doctors.index"
      :filters="filters"
      search-placeholder="Doctor Name, NMC Number, Phone, Specialization..."
  />

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full border-collapse">
        <thead class="bg-gray-100 dark:bg-gray-700 border-b dark:border-gray-600">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 w-12">#</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Name</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">NMC Number</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Specialization</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Phone</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Availability</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Registered (BS)</th>
            <th v-if="canEdit || canDelete" class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 w-36">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(d, index) in doctors.data":key="d.id ?? index" class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{
    ((doctors.current_page - 1) * doctors.per_page)
    + index + 1
}}</td>
            <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-100"> <i class="fas fa-user-md text-indigo-500 mr-1"></i>
              {{ d.name || '--' }}</td>
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ d.nmc_number || '--' }}</td>
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ d.specialization || '--' }}</td>
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ d.phone || '--' }}</td>

            <!-- Availability Days from Schedules -->
            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
              <div v-if="d.schedules?.length" class="flex flex-wrap gap-1">
                <span v-for="s in d.schedules" :key="s.id"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                  {{ s.day?.slice(0, 3) }}
                </span>
              </div>
              <span v-else class="text-gray-400 dark:text-gray-500 italic">--</span>
            </td>

            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ d.created_at_bs || '--' }}</td>

            <!-- Actions -->
            <td v-if="canEdit || canDelete" class="px-6 py-4 flex gap-3 text-lg">
              <Link :href="route('doctors.show', d.id)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" title="Show">
                <i class="fas fa-eye"></i>
              </Link>

              <button v-if="canEdit" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300" @click="openEditModal(d)"
                title="Edit">
                <i class="fas fa-edit"></i>
              </button>

            </td>
          </tr>

        <tr v-if="!doctors.data.length">
            <td :colspan="canEdit || canDelete ? 8 : 7" class="text-center py-10 text-gray-500 dark:text-gray-400">No doctors found</td>
          </tr>
        </tbody>
      </table>
      <div v-if="doctors?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
          <div class="text-sm text-gray-600 dark:text-gray-400">
              Showing <span class="font-medium dark:text-gray-200">{{ doctors.from || 0 }}</span>
              to <span class="font-medium dark:text-gray-200">{{ doctors.to || 0 }}</span>
              of <span class="font-medium dark:text-gray-200">{{ doctors.total }}</span> results
          </div>
          <Pagination :links="doctors.links" />
      </div>
    </div>

    <!-- Modals -->
    <CreateDoctorModal v-if="showCreateModal" @close="showCreateModal = false" @success="refreshData"/>
    <EditDoctorModal v-if="selectedItem" :doctor="selectedItem" @close="selectedItem = null" @success="refreshData"/>
    <DeleteDoctorModal v-if="deleteItem" :doctor="deleteItem" @close="deleteItem = null" @success="refreshData" />

  </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'

import CreateDoctorModal from './CreateModal.vue'
import EditDoctorModal from './EditModal.vue'
import DeleteDoctorModal from './DeleteModal.vue'

const props = defineProps({
    doctors: Object,
    filters: {
        type: Object,
        default: () => ({})
    },
    can: {
        type: Object,
        default: () => ({})
    }
})

const canCreate = props.can?.create ?? true
const canEdit = props.can?.edit ?? true
const canDelete = props.can?.delete ?? true

const refreshData = () => {
    router.reload({
        only: ['doctors'],
    })
}

const showCreateModal = ref(false)
const selectedItem = ref(null)
const deleteItem = ref(null)

const openCreateModal = () => showCreateModal.value = true
const openEditModal = (doctor) => selectedItem.value = doctor
const openDeleteModal = (doctor) => deleteItem.value = doctor
</script>