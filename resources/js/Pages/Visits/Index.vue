<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FilterBar from '@/Components/FilterBar.vue'
import Pagination from '@/Components/Pagination.vue'
import CreateVisitModal from './CreateModal.vue'
import EditVisitModal from './EditModal.vue'
import CancelModal from './CancelModal.vue'
import ShowVisitModal from './ShowModal.vue'

const props = defineProps({
  visits: Object,
  patients: Array,
  doctors: Array,
  appointments: Array,
  filters: Object,
  can: Object,
})

const showCreateModal = ref(false)
const selectedVisit = ref(null)
const deleteVisit = ref(null)
const showVisit = ref(null)

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatType = (visit) => visit.appointment_id ? 'Appointment' : 'Walk-in'
const typeClass = (visit) => visit.appointment_id ? 'bg-indigo-100 text-indigo-700' : 'bg-green-100 text-green-700'

const permissions = computed(() => {
  const c = props.can && Object.keys(props.can).length ? props.can : null
  return c ?? { create: true, edit: true, delete: true }
})
const canEdit = computed(() => permissions.value?.edit !== false)

const refreshData = () => { router.reload({ only: ['visits'] }) }
</script>

<template>
  <AuthenticatedLayout>
    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-notes-medical text-indigo-600"></i>
            Visits
          </h1>
          <p class="text-sm text-gray-600">Manage patient visits and consultation records</p>
        </div>
        <button @click="showCreateModal = true"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow">
          + New Visit
        </button>
      </div>

      <FilterBar
        route-name="visits.index"
        :filters="filters"
        search-placeholder="Patient name, phone, symptoms..."
      />

      <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">#</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Patient</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Doctor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Symptoms</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Date</th>
                <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600">Type</th>
                <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-gray-600">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(visit, index) in visits?.data || []" :key="visit.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">{{ ((visits.current_page - 1) * visits.per_page) + index + 1 }}</td>
                <td class="px-6 py-4">
                  <div class="font-medium">{{ visit.patient?.name || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">{{ visit.patient?.phone || '-' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm">Dr. {{ visit.doctor?.name || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">{{ visit.doctor?.specialization || '-' }}</div>
                </td>
                <td class="px-6 py-4 text-sm">{{ visit.chief_complaint || '-' }}</td>
                <td class="px-6 py-4 text-sm">{{ formatDate(visit.visited_at) }}</td>
                <td class="px-6 py-4">
                  <span :class="typeClass(visit)" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold">
                    {{ formatType(visit) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-end items-center gap-3">
                    <button class="text-blue-600 hover:text-blue-800" @click="showVisit = visit" title="Show">
                      <i class="fas fa-eye"></i>
                    </button>
                    <Link :href="route('consultations.create', visit.id)"
                      class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2 py-1 text-xs font-semibold text-white hover:bg-emerald-700">
                      <i class="fas fa-stethoscope"></i> Start Consultation
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!visits?.data?.length">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No visits found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="visits?.data?.length" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t px-6 py-4">
          <div class="text-sm text-gray-600">
            Showing <span class="font-medium">{{ visits.from || 0 }}</span>
            to <span class="font-medium">{{ visits.to || 0 }}</span>
            of <span class="font-medium">{{ visits.total }}</span> results
          </div>
          <Pagination :links="visits.links" />
        </div>
      </div>

      <CreateVisitModal v-if="showCreateModal" :patients="patients" :doctors="doctors" :appointments="appointments"
        @close="showCreateModal = false" @success="refreshData" />
      <ShowVisitModal v-if="showVisit" :visit="showVisit" :doctors="doctors" @close="showVisit = null" />
      <EditVisitModal v-if="selectedVisit" :visit="selectedVisit" :doctors="doctors" @close="selectedVisit = null" @success="refreshData" />
      <CancelModal v-if="deleteVisit" :visit="deleteVisit" :doctors="doctors" @close="deleteVisit = null" @success="refreshData" />
    </div>
  </AuthenticatedLayout>
</template>