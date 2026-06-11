<template>
  <DoctorLayout title="Dashboard">
    <!-- Alert Bar -->
    <div v-if="pendingLabsAlert.length" class="alert-bar alert-amber" role="alert">
      <i class="ti ti-flask" />
      <strong>{{ pendingLabsAlert.length }} pending lab results</strong> need your review —
      {{ pendingLabsAlert.map(l => l.patient_name).join(', ') }}
      <!-- <Link :href="route('doctor.laboratory.results')" class="alert-link">View all</Link> -->
    </div>

    <div v-if="overdueFollowups.length" class="alert-bar alert-coral" role="alert">
      <i class="ti ti-clock-exclamation" />
      <strong>{{ overdueFollowups.length }} follow-ups overdue</strong> —
      {{ overdueFollowups.map(f => f.patient_name).join(', ') }}
      <!-- <Link :href="route('doctor.followups.index')" class="alert-link">View all</Link> -->
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
      <StatCard
        label="Today's Appointments"
        :value="stats.todayAppointments"
        :sub="`${stats.remainingAppointments} remaining`"
        icon="ti-calendar"
        color="teal"
      />
      <StatCard
        label="Waiting Patients"
        :value="stats.waitingPatients"
        :sub="`Avg wait ${stats.avgWaitMinutes} min`"
        icon="ti-hourglass"
        color="amber"
      />
      <StatCard
        label="Consultations Today"
        :value="stats.consultationsToday"
        sub="Completed"
        icon="ti-stethoscope"
        color="teal"
      />
      <StatCard
        label="Pending Labs"
        :value="stats.pendingLabs"
        sub="Awaiting review"
        icon="ti-flask"
        color="coral"
      />
      <StatCard
        label="Follow Ups Due"
        :value="stats.followupsDue"
        :sub="`${stats.overdueFollowups} overdue`"
        icon="ti-repeat"
        color="amber"
      />
      <StatCard
        label="My Patients"
        :value="stats.totalPatients"
        sub="Total assigned"
        icon="ti-users"
        color="blue"
      />
    </div>

    <!-- Queue + Side panels -->
    <div class="dashboard-grid">
      <!-- Patient Queue -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="ti ti-list-numbers" style="color:#1D9E75" />
            Patient Queue
          </h2>
          <Link :href="route('doctor.visits.today')" class="card-link">Full Queue →</Link>
        </div>
        <PatientQueue :queue="queue" :limit="5" />
      </div>

      <!-- Right Column -->
      <div class="side-col">
        <!-- Follow Ups -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <i class="ti ti-repeat" style="color:#BA7517" />
              Follow Ups Due
            </h2>
            <span class="badge badge-coral">{{ followups.length }} due</span>
          </div>
          <div class="followup-list">
            <div
              v-for="fu in followups"
              :key="fu.id"
              class="followup-row"
            >
              <span class="fu-dot" :class="fu.urgency" />
              <div>
                <div class="fu-name">{{ fu.patient_name }}</div>
                <div class="fu-detail">{{ fu.type }} — {{ fu.due_label }}</div>
              </div>
              <span class="badge ms-auto" :class="`badge-${fu.urgency}`">{{ fu.urgency_label }}</span>
            </div>
            <div v-if="!followups.length" class="empty-state">
              <i class="ti ti-check" /> All caught up
            </div>
          </div>
        </div>

        <!-- Recent Prescriptions -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">
              <i class="ti ti-pill" style="color:#185FA5" />
              Recent Prescriptions
            </h2>
            <Link :href="route('doctor.prescriptions.index')" class="card-link">View all</Link>
          </div>
          <div class="rx-list">
            <div v-for="rx in recentPrescriptions" :key="rx.id" class="rx-row">
              <i class="ti ti-pill" style="color:#185FA5;font-size:14px" />
              <div>
                <div class="rx-drug">{{ rx.drugs_summary }}</div>
                <div class="rx-patient">{{ rx.patient_name }} · {{ rx.created_at_label }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Today Summary Tiles -->
    <div class="card" style="margin-top:14px">
      <div class="card-header">
        <h2 class="card-title">Today's Summary</h2>
      </div>
      <div class="summary-tiles">
        <div class="summary-tile teal">
          <div class="tile-val">{{ stats.consultationsToday }}</div>
          <div class="tile-label">Consultations Done</div>
        </div>
        <div class="summary-tile amber">
          <div class="tile-val">{{ stats.pendingConsultations }}</div>
          <div class="tile-label">Pending</div>
        </div>
        <div class="summary-tile blue">
          <div class="tile-val">{{ stats.labResultsToday }}</div>
          <div class="tile-label">Lab Results</div>
        </div>
        <div class="summary-tile coral">
          <div class="tile-val">{{ stats.followupsDue }}</div>
          <div class="tile-label">Follow Ups Due</div>
        </div>
      </div>
    </div>
  </DoctorLayout>
</template>

<script setup>
import { computed } from 'vue'

import { Link } from '@inertiajs/vue3'
// import { route } from 'ziggy-js'
import DoctorLayout from '@/Layouts/DoctorLayout.vue'
// import StatCard from '@/Components/Doctor/StatCard.vue'
// import PatientQueue from '@/Components/Doctor/PatientQueue.vue'

const props = defineProps({
  stats: Object,
  queue: Array,
  followups: Array,
  recentPrescriptions: Array,
  pendingLabsAlert: { type: Array, default: () => [] },
  overdueFollowups: { type: Array, default: () => [] },
})
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 10px;
  margin-bottom: 16px;
}
@media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px)  { .stats-grid { grid-template-columns: repeat(2, 1fr); } }

.dashboard-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 14px;
}
@media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }

.side-col { display: flex; flex-direction: column; gap: 14px; }

.card {
  background: #fff;
  border: 1px solid #e8eaed;
  border-radius: 12px;
  padding: 14px 16px;
}
.card-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 12px;
}
.card-title {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; font-weight: 600; color: #111827; margin: 0;
}
.card-link { font-size: 11.5px; color: #0F6E56; text-decoration: none; }
.card-link:hover { text-decoration: underline; }

.alert-bar {
  display: flex; align-items: center; gap: 8px;
  padding: 9px 14px; border-radius: 8px;
  font-size: 12.5px; margin-bottom: 12px;
}
.alert-amber { background: #FAEEDA; border: 1px solid #EF9F27; color: #633806; }
.alert-coral  { background: #FAECE7; border: 1px solid #D85A30; color: #4A1B0C; }
.alert-link { margin-left: auto; font-weight: 600; text-decoration: underline; cursor: pointer; color: inherit; }

.badge {
  display: inline-flex; align-items: center;
  padding: 2px 8px; border-radius: 20px;
  font-size: 10.5px; font-weight: 600;
}
.badge-coral  { background: #FAECE7; color: #993C1D; }
.badge-amber  { background: #FAEEDA; color: #BA7517; }
.badge-teal   { background: #E1F5EE; color: #0F6E56; }
.badge-overdue { background: #FAECE7; color: #993C1D; }
.badge-today  { background: #FAEEDA; color: #BA7517; }
.badge-upcoming { background: #E6F1FB; color: #185FA5; }
.ms-auto { margin-left: auto; }

.followup-list { display: flex; flex-direction: column; }
.followup-row {
  display: flex; align-items: center; gap: 10px;
  padding: 7px 0; border-bottom: 1px solid #f3f4f6;
}
.followup-row:last-child { border-bottom: none; }
.fu-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.fu-dot.overdue  { background: #E24B4A; }
.fu-dot.today    { background: #EF9F27; }
.fu-dot.upcoming { background: #1D9E75; }
.fu-name { font-size: 12px; font-weight: 600; color: #111827; }
.fu-detail { font-size: 11px; color: #6b7280; }

.rx-list { display: flex; flex-direction: column; }
.rx-row {
  display: flex; align-items: flex-start; gap: 8px;
  padding: 7px 0; border-bottom: 1px solid #f3f4f6; font-size: 12px;
}
.rx-row:last-child { border-bottom: none; }
.rx-drug { font-weight: 600; color: #111827; }
.rx-patient { font-size: 11px; color: #6b7280; margin-top: 1px; }

.empty-state { text-align: center; padding: 16px; color: #9ca3af; font-size: 12px; }

.summary-tiles {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;
}
@media (max-width: 768px) { .summary-tiles { grid-template-columns: repeat(2, 1fr); } }
.summary-tile {
  text-align: center; padding: 14px 10px;
  border-radius: 10px;
}
.summary-tile.teal   { background: #E1F5EE; }
.summary-tile.amber  { background: #FAEEDA; }
.summary-tile.blue   { background: #E6F1FB; }
.summary-tile.coral  { background: #FAECE7; }
.tile-val  { font-size: 24px; font-weight: 700; }
.summary-tile.teal  .tile-val { color: #0F6E56; }
.summary-tile.amber .tile-val { color: #BA7517; }
.summary-tile.blue  .tile-val { color: #185FA5; }
.summary-tile.coral .tile-val { color: #993C1D; }
.tile-label { font-size: 11px; margin-top: 3px; }
.summary-tile.teal  .tile-label { color: #0F6E56; }
.summary-tile.amber .tile-label { color: #BA7517; }
.summary-tile.blue  .tile-label { color: #185FA5; }
.summary-tile.coral .tile-label { color: #993C1D; }
</style>