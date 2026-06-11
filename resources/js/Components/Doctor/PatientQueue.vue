<template>
  <DoctorLayout title="My Patients">
    <div class="page-header">
      <div>
        <h1 class="page-title">My Patients</h1>
        <p class="page-sub">
          <i class="ti ti-shield-check" style="color:#0F6E56" />
          Showing only patients assigned to you — {{ $page.props.auth?.doctor?.name ?? 'Doctor' }}
        </p>
      </div>
      <!-- <Link :href="route('doctor.patients.create')" class="btn btn-primary">
        <i class="ti ti-plus" /> Add Patient
      </Link> -->
    </div>

    <!-- Filters -->
    <div class="filter-bar">
      <div class="search-wrap">
        <i class="ti ti-search search-icon" />
        <input
          v-model="search"
          type="text"
          class="search-input"
          placeholder="Search by name, phone, patient no…"
          @input="debouncedSearch"
        />
      </div>
      <select v-model="filters.condition" class="filter-select" @change="applyFilters">
        <option value="">All Conditions</option>
        <option v-for="c in conditionOptions" :key="c" :value="c">{{ c }}</option>
      </select>
      <select v-model="filters.status" class="filter-select" @change="applyFilters">
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="stable">Stable</option>
        <option value="critical">Critical</option>
        <option value="discharged">Discharged</option>
      </select>
      <select v-model="filters.sort" class="filter-select" @change="applyFilters">
        <option value="last_visit">Last Visit</option>
        <option value="name">Name</option>
        <option value="next_visit">Next Visit</option>
      </select>
    </div>

    <!-- Stats Row -->
    <div class="patient-stats">
      <div class="pstat"><span class="pstat-val">{{ patients.total }}</span><span class="pstat-label">Total</span></div>
      <div class="pstat"><span class="pstat-val teal">{{ patients.active }}</span><span class="pstat-label">Active</span></div>
      <div class="pstat"><span class="pstat-val amber">{{ patients.followup }}</span><span class="pstat-label">Follow Up Due</span></div>
      <div class="pstat"><span class="pstat-val coral">{{ patients.critical }}</span><span class="pstat-label">Critical</span></div>
    </div>

    <!-- Table -->
    <div class="table-card">
      <table class="patients-table" aria-label="My assigned patients">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Age / Gender</th>
            <th>Condition</th>
            <th>Last Visit</th>
            <th>Next Visit</th>
            <th>Status</th>
            <th style="width:130px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="patient in patients.data" :key="patient.id">
            <td>
              <div class="patient-cell">
                <div class="p-avatar" :style="{ background: patient.avatar_bg, color: patient.avatar_color }">
                  {{ patient.initials }}
                </div>
                <div>
                  <div class="p-name">{{ patient.name }}</div>
                  <div class="p-no">#{{ patient.patient_no }}</div>
                </div>
              </div>
            </td>
            <td>{{ patient.age }} / {{ patient.gender }}</td>
            <td>
              <span v-for="c in patient.conditions.slice(0,2)" :key="c" class="cond-tag">{{ c }}</span>
              <span v-if="patient.conditions.length > 2" class="more-tag">+{{ patient.conditions.length - 2 }}</span>
            </td>
            <td>{{ patient.last_visit_label }}</td>
            <td :class="{ 'overdue': patient.next_visit_overdue }">{{ patient.next_visit_label ?? '—' }}</td>
            <td><span class="status-pill" :class="patient.status">{{ patient.status_label }}</span></td>
            <td>
              <div class="row-actions">
                <Link :href="route('doctor.consultations.create', { patient: patient.id })" class="btn btn-sm btn-primary">
                  Consult
                </Link>
                <Link :href="route('doctor.patients.show', patient.id)" class="btn btn-sm">
                  View
                </Link>
              </div>
            </td>
          </tr>
          <tr v-if="!patients.data?.length">
            <td colspan="7" style="text-align:center;padding:32px;color:#9ca3af">No patients found.</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination" v-if="patients.links?.length > 3">
        <Link
          v-for="link in patients.links"
          :key="link.label"
          :href="link.url ?? '#'"
          class="page-link"
          :class="{ active: link.active, disabled: !link.url }"
          v-html="link.label"
        />
      </div>
    </div>
  </DoctorLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
// import { route } from 'ziggy-js'
import DoctorLayout from '@/Layouts/DoctorLayout.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

console.log(page.props)
const props = defineProps({
  patients: Object,
  conditionOptions: { type: Array, default: () => [] },
  filters: Object,
})

const search = ref(props.filters?.search ?? '')
const filters = reactive({
  condition: props.filters?.condition ?? '',
  status:    props.filters?.status    ?? '',
  sort:      props.filters?.sort      ?? 'last_visit',
})

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(applyFilters, 350)
}

function applyFilters() {
  router.get(route('doctor.patients.index'), {
    search: search.value,
    ...filters,
  }, { preserveState: true, replace: true })
}
</script>

<style scoped>
.page-header {
  display: flex; justify-content: space-between; align-items: flex-start;
  margin-bottom: 16px;
}
.page-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; }
.page-sub { font-size: 12px; color: #0F6E56; margin-top: 3px; display: flex; align-items: center; gap: 4px; }

.filter-bar {
  display: flex; gap: 10px; align-items: center;
  margin-bottom: 14px; flex-wrap: wrap;
}
.search-wrap {
  flex: 1; min-width: 200px;
  display: flex; align-items: center;
  border: 1px solid #d1d5db; border-radius: 8px;
  background: #fff; padding: 0 10px; gap: 6px;
}
.search-icon { color: #9ca3af; font-size: 14px; }
.search-input {
  flex: 1; border: none; outline: none; padding: 7px 0;
  font-size: 13px; background: transparent; font-family: inherit;
}
.filter-select {
  border: 1px solid #d1d5db; border-radius: 8px;
  padding: 7px 10px; font-size: 13px; font-family: inherit;
  background: #fff; color: #374151;
}

.patient-stats {
  display: flex; gap: 10px; margin-bottom: 14px;
}
.pstat {
  background: #fff; border: 1px solid #e8eaed; border-radius: 10px;
  padding: 10px 18px; display: flex; flex-direction: column; align-items: center;
}
.pstat-val { font-size: 22px; font-weight: 700; color: #111827; }
.pstat-val.teal  { color: #0F6E56; }
.pstat-val.amber { color: #BA7517; }
.pstat-val.coral { color: #993C1D; }
.pstat-label { font-size: 11px; color: #6b7280; margin-top: 1px; }

.table-card {
  background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;
}
.patients-table { width: 100%; border-collapse: collapse; }
.patients-table th {
  font-size: 11px; font-weight: 600; color: #6b7280; text-align: left;
  padding: 10px 14px; border-bottom: 1px solid #e8eaed;
  background: #f9fafb; text-transform: uppercase; letter-spacing: .04em;
}
.patients-table td { padding: 11px 14px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; }
.patients-table tr:last-child td { border-bottom: none; }
.patients-table tr:hover td { background: #f9fafb; }

.patient-cell { display: flex; align-items: center; gap: 10px; }
.p-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.p-name { font-size: 13px; font-weight: 600; color: #111827; }
.p-no { font-size: 11px; color: #9ca3af; }

.cond-tag {
  display: inline-flex; background: #f3f4f6; color: #374151;
  padding: 2px 7px; border-radius: 20px; font-size: 10.5px; font-weight: 500; margin-right: 3px;
}
.more-tag { font-size: 10.5px; color: #9ca3af; }
.overdue { color: #E24B4A; font-weight: 600; }

.status-pill {
  display: inline-flex; align-items: center;
  padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.status-pill.active    { background: #E1F5EE; color: #0F6E56; }
.status-pill.stable    { background: #EAF3DE; color: #3B6D11; }
.status-pill.critical  { background: #FAECE7; color: #993C1D; }
.status-pill.followup  { background: #FAEEDA; color: #BA7517; }
.status-pill.discharged { background: #f3f4f6; color: #6b7280; }

.row-actions { display: flex; gap: 6px; }
.btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 14px; border-radius: 8px; font-size: 12.5px;
  font-family: inherit; cursor: pointer; border: 1px solid #e5e7eb;
  background: #fff; color: #374151; text-decoration: none; transition: all .15s;
}
.btn:hover { background: #f9fafb; }
.btn-primary { background: #0F6E56; color: #fff; border-color: #0F6E56; }
.btn-primary:hover { background: #1D9E75; border-color: #1D9E75; }
.btn-sm { padding: 5px 10px; font-size: 12px; }

.pagination {
  display: flex; align-items: center; gap: 4px;
  padding: 12px 14px; border-top: 1px solid #f3f4f6;
}
.page-link {
  padding: 5px 10px; border-radius: 6px; font-size: 12.5px;
  text-decoration: none; color: #374151; border: 1px solid #e5e7eb;
  transition: all .15s;
}
.page-link:hover { background: #f9fafb; }
.page-link.active { background: #0F6E56; color: #fff; border-color: #0F6E56; }
.page-link.disabled { opacity: .4; pointer-events: none; }
</style>