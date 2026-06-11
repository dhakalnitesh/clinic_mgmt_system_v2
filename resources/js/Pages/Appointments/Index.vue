<template>
  <AuthenticatedLayout>
    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <i class="fas fa-calendar-check text-indigo-600"></i>
            Appointments
          </h1>
          <p class="text-sm text-gray-600 dark:text-gray-400">Manage patient appointments and scheduling</p>
        </div>
        <button v-if="canCreate" @click="openCreateModal"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow">
          + Create
        </button>
      </div>

      <FilterBar
        route-name="appointments.index"
        :filters="filters"
        search-placeholder="Patient name, doctor name..."
      />

      <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-100 dark:bg-gray-700 border-b dark:border-gray-600">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">#</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Patient</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Doctor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Date (BS)</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Time</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Status</th>
                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="(a, index) in appointments?.data || []" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 text-sm dark:text-gray-300">{{ ((appointments.current_page - 1) * appointments.per_page) + index + 1 }}</td>
                <td class="px-6 py-4 font-sm dark:text-gray-200"><i class="fas fa-user text-indigo-400 mr-1"></i>{{ a.patient?.name || '--' }}</td>
                <td class="px-6 py-4 text-sm dark:text-gray-300"><i class="fas fa-user-md text-indigo-700 mr-1"></i>{{ a.doctor?.name || '--' }}</td>
                <td class="px-6 py-4 text-sm dark:text-gray-300">{{ a.appointment_date || '--' }}</td>
                <td class="px-6 py-4 text-sm dark:text-gray-300">{{ a.appointment_time || '--' }}</td>
                <td class="px-6 py-4 text-sm dark:text-gray-300">
                  <span class="px-2 py-1 rounded text-xs font-semibold" :class="statusClass(a.status)">
                    {{ a.status || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-end gap-3">
                    <button v-if="a.status !== 'completed' && a.status !== 'cancelled' && a.status !== 'visited'"
                      class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300" @click="askConfirm('visit', a)" title="Mark Visited">
                      <i class="fas fa-check-circle"></i>
                    </button>
                    <button v-if="a.status !== 'cancelled' && a.status !== 'completed' && a.status !== 'visited'"
                      class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" @click="askConfirm('cancel', a)" title="Cancel">
                      <i class="fas fa-times-circle"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!appointments?.data?.length">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No appointments found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="appointments?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t dark:border-gray-700 px-6 py-4">
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Showing <span class="font-medium dark:text-gray-200">{{ appointments.from || 0 }}</span>
            to <span class="font-medium dark:text-gray-200">{{ appointments.to || 0 }}</span>
            of <span class="font-medium dark:text-gray-200">{{ appointments.total }}</span> results
          </div>
          <Pagination :links="appointments.links" />
        </div>
      </div>

      <div v-if="confirmAction" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Confirm Action</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Are you sure you want to
            <span class="font-semibold">{{ confirmAction.type === 'visit' ? 'mark this appointment as VISITED' : 'CANCEL this appointment' }}</span>?
          </p>
          <div class="flex justify-end gap-3 mt-6">
            <button @click="closeConfirm" class="px-4 py-2 rounded-lg border dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">No</button>
            <button @click="executeAction" :class="confirmAction.type === 'visit' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
              class="px-4 py-2 rounded-lg text-white">Yes, Confirm</button>
          </div>
        </div>
      </div>

      <CreateAppointmentModal v-if="showCreateModal" :patients="patients" :doctors="doctors"
        @close="showCreateModal = false" @success="refreshData" />
      <CancelAppointmentModal v-if="deleteItem" :appointment="deleteItem"
        @close="deleteItem = null" @success="refreshData" />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'
import CreateAppointmentModal from './CreateModal.vue'
import CancelAppointmentModal from './CancelModal.vue'

const props = defineProps({
  patients: [Array, Object],
  doctors: [Array, Object],
  appointments: [Array, Object],
  can: { type: Object, default: () => ({ create: true }) },
  filters: Object,
})

const canCreate = ref(props.can?.create !== false)
const showCreateModal = ref(false)
const deleteItem = ref(null)
const confirmAction = ref(null)
const openCreateModal = () => { showCreateModal.value = true }

const statusClass = (status) => {
  switch ((status || '').toLowerCase()) {
    case 'completed': return 'bg-green-100 text-green-700'
    case 'cancelled': return 'bg-red-100 text-red-700'
    case 'visited': return 'bg-blue-100 text-blue-700'
    default: return 'bg-yellow-100 text-yellow-700'
  }
}

const askConfirm = (type, item) => { confirmAction.value = { type, item } }
const closeConfirm = () => { confirmAction.value = null }

const executeAction = () => {
  const { type, item } = confirmAction.value
  if (type === 'visit') {
    router.patch(route('appointments.updateStatus', item.id), { status: 'visited' }, {
      preserveScroll: true,
      onSuccess: () => { item.status = 'visited'; closeConfirm() }
    })
  }
  if (type === 'cancel') {
    router.patch(route('appointments.cancel', item.id), { status: 'cancelled' }, {
      preserveScroll: true,
      onSuccess: () => { item.status = 'cancelled'; closeConfirm() }
    })
  }
}

const refreshData = () => { router.reload({ only: ['appointments'] }) }
</script>