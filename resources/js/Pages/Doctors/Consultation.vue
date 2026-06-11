<template>
  <DoctorLayout :title="`Consultation — ${visit.patient.name}`">
    <!-- Patient Header Bar -->
    <div class="consult-header">
      <div class="patient-identity">
        <div class="token-badge active">{{ visit.queue_token }}</div>
        <div class="patient-avatar">{{ initials }}</div>
        <div>
          <div class="patient-name">
            {{ visit.patient.name }}
            <span class="patient-meta">· Age {{ visit.patient.age }} · {{ visit.patient.gender }} · #{{ visit.patient.patient_no }}</span>
          </div>
          <div class="patient-conditions">
            <span v-for="c in visit.patient.conditions" :key="c" class="badge badge-condition">{{ c }}</span>
            <span v-if="visit.patient.allergies?.length" class="badge badge-allergy">
              <i class="ti ti-alert-triangle" /> Allergic: {{ visit.patient.allergies.join(', ') }}
            </span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <span class="badge badge-active"><i class="ti ti-activity" /> In Consultation</span>
        <button class="btn btn-sm" @click="printPrescription"><i class="ti ti-printer" /> Print</button>
        <button class="btn btn-sm btn-danger" @click="confirmEndVisit"><i class="ti ti-check" /> Complete Visit</button>
      </div>
    </div>

    <!-- Main Consultation Layout -->
    <div class="consult-layout">

      <!-- Left Panel: Vitals + Patient Info + History -->
      <aside class="consult-left">
        <!-- Vitals -->
        <div class="panel">
          <div class="panel-header">
            <span>Vitals</span>
            <button class="btn btn-xs" @click="showVitalsEdit = !showVitalsEdit">
              <i class="ti ti-edit" />
            </button>
          </div>
          <div class="vitals-grid">
            <VitalBox
              v-for="v in vitals"
              :key="v.key"
              :label="v.label"
              :value="form.vitals[v.key]"
              :unit="v.unit"
              :status="v.status"
              :editable="showVitalsEdit"
              @update="form.vitals[v.key] = $event"
            />
          </div>
        </div>

        <!-- Patient Info -->
        <div class="panel">
          <div class="panel-header">Patient Info</div>
          <div class="info-table">
            <div class="info-row"><span>Blood Group</span><span>{{ visit.patient.blood_group }}</span></div>
            <div class="info-row"><span>Phone</span><span>{{ visit.patient.phone }}</span></div>
            <div class="info-row"><span>Insurance</span><span>{{ visit.patient.insurance ?? '—' }}</span></div>
            <div class="info-row"><span>Last Visit</span><span>{{ visit.patient.last_visit_label }}</span></div>
          </div>
        </div>

        <!-- Past History -->
        <div class="panel">
          <div class="panel-header">Medical History</div>
          <div class="history-chips">
            <span v-for="h in visit.patient.medical_history" :key="h" class="badge badge-history">{{ h }}</span>
            <span v-if="!visit.patient.medical_history?.length" class="text-muted">None recorded</span>
          </div>
          <div class="panel-divider" />
          <div class="panel-header" style="margin-top:8px">Current Medications</div>
          <div
            v-for="med in visit.patient.current_medications"
            :key="med"
            class="med-row"
          >
            <i class="ti ti-pill" style="color:#185FA5;font-size:13px" />
            {{ med }}
          </div>
          <span v-if="!visit.patient.current_medications?.length" class="text-muted">None</span>
        </div>

        <!-- Previous Visits -->
        <div class="panel">
          <div class="panel-header">
            Previous Visits
            <Link :href="route('doctor.patients.show', visit.patient.id)" class="card-link">All history</Link>
          </div>
          <div
            v-for="pv in visit.patient.previous_visits"
            :key="pv.id"
            class="prev-visit-row"
          >
            <div class="pv-date">{{ pv.date }}</div>
            <div class="pv-diag">{{ pv.diagnosis }}</div>
          </div>
          <div v-if="!visit.patient.previous_visits?.length" class="text-muted">First visit</div>
        </div>
      </aside>

      <!-- Right Panel: SOAP Tabs -->
      <div class="consult-main">
        <div class="soap-tabs" role="tablist">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            class="soap-tab"
            :class="{ active: activeTab === tab.key, done: tab.done }"
            role="tab"
            :aria-selected="activeTab === tab.key"
            @click="activeTab = tab.key"
          >
            <i :class="`ti ${tab.icon}`" />
            {{ tab.label }}
            <i v-if="tab.done" class="ti ti-circle-check tab-done-icon" />
          </button>
        </div>

        <!-- S — Subjective -->
        <div v-show="activeTab === 'subjective'" class="tab-panel">
          <div class="field-group">
            <label class="field-label">Chief Complaint <span class="required">*</span></label>
            <textarea v-model="form.subjective.chief_complaint" rows="2" class="field-textarea" placeholder="Patient's main complaint in their own words…" />
          </div>
          <div class="field-group">
            <label class="field-label">History of Present Illness</label>
            <textarea v-model="form.subjective.hpi" rows="4" class="field-textarea" placeholder="Onset, duration, character, aggravating/relieving factors…" />
          </div>
          <div class="field-group">
            <label class="field-label">Review of Systems</label>
            <textarea v-model="form.subjective.ros" rows="2" class="field-textarea" placeholder="Systematic review notes…" />
          </div>
          <div class="field-group">
            <label class="field-label">Patient Reported Allergies</label>
            <input v-model="form.subjective.allergies" type="text" class="field-input" placeholder="e.g. Penicillin, Aspirin" />
          </div>
        </div>

        <!-- O — Objective -->
        <div v-show="activeTab === 'objective'" class="tab-panel">
          <div class="field-group">
            <label class="field-label">General Appearance</label>
            <textarea v-model="form.objective.general" rows="2" class="field-textarea" placeholder="Alert and oriented, no acute distress…" />
          </div>
          <div class="field-group">
            <label class="field-label">Systemic Examination</label>
            <textarea v-model="form.objective.systems_exam" rows="5" class="field-textarea" placeholder="CVS, RS, Abdomen, CNS findings…" />
          </div>
          <div class="field-group">
            <label class="field-label">Investigation Notes</label>
            <textarea v-model="form.objective.investigations" rows="2" class="field-textarea" placeholder="Relevant test findings at this visit…" />
          </div>
        </div>

        <!-- A — Assessment / Diagnosis -->
        <div v-show="activeTab === 'diagnosis'" class="tab-panel">
          <div class="field-group">
            <label class="field-label">Primary Diagnosis <span class="required">*</span></label>
            <div class="icd-search-wrap">
              <input
                v-model="form.assessment.primary_diagnosis"
                type="text"
                class="field-input"
                placeholder="Search ICD-10 code or condition name…"
                @input="searchICD"
              />
              <div v-if="icdSuggestions.length" class="icd-dropdown">
                <div
                  v-for="s in icdSuggestions"
                  :key="s.code"
                  class="icd-item"
                  @click="selectICD(s, 'primary')"
                >
                  <strong>{{ s.code }}</strong> — {{ s.label }}
                </div>
              </div>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Secondary Diagnoses</label>
            <input v-model="form.assessment.secondary_diagnoses" type="text" class="field-input" placeholder="Additional diagnoses…" />
          </div>
          <div class="field-group">
            <label class="field-label">Clinical Impression / Plan</label>
            <textarea v-model="form.assessment.clinical_notes" rows="5" class="field-textarea" placeholder="Your clinical impression and management plan…" />
          </div>
        </div>

        <!-- P — Prescription -->
        <div v-show="activeTab === 'prescription'" class="tab-panel">
          <div class="section-actions">
            <h3 class="section-title">Prescription</h3>
            <button class="btn btn-primary btn-sm" @click="addDrug">
              <i class="ti ti-plus" /> Add Drug
            </button>
          </div>

          <div class="rx-table-wrap">
            <table class="rx-table">
              <thead>
                <tr>
                  <th>Drug / Strength</th>
                  <th>Dose</th>
                  <th>Frequency</th>
                  <th>Duration</th>
                  <th>Route</th>
                  <th style="width:36px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(drug, idx) in form.prescription.drugs" :key="idx">
                  <td>
                    <input v-model="drug.name" type="text" class="table-input" placeholder="Drug name + strength" />
                  </td>
                  <td>
                    <input v-model="drug.dose" type="text" class="table-input" placeholder="e.g. 5mg" style="width:70px" />
                  </td>
                  <td>
                    <select v-model="drug.frequency" class="table-select">
                      <option>OD</option><option>BD</option><option>TDS</option><option>QDS</option>
                      <option>HS</option><option>PRN</option><option>SOS</option><option>Stat</option>
                    </select>
                  </td>
                  <td>
                    <input v-model="drug.duration" type="text" class="table-input" placeholder="5 days" style="width:80px" />
                  </td>
                  <td>
                    <select v-model="drug.route" class="table-select">
                      <option>Oral</option><option>IV</option><option>IM</option><option>Topical</option>
                      <option>Sublingual</option><option>Inhaled</option>
                    </select>
                  </td>
                  <td>
                    <button class="btn-icon-del" @click="removeDrug(idx)"><i class="ti ti-trash" /></button>
                  </td>
                </tr>
                <tr v-if="!form.prescription.drugs.length">
                  <td colspan="6" class="empty-row">No drugs added yet. Click "Add Drug" to start.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="field-group" style="margin-top:12px">
            <label class="field-label">Special Instructions</label>
            <textarea v-model="form.prescription.instructions" rows="2" class="field-textarea" placeholder="Take with food, avoid alcohol, home BP monitoring…" />
          </div>

          <div class="rx-footer">
            <button class="btn btn-primary" @click="printPrescription">
              <i class="ti ti-printer" /> Print Prescription
            </button>
            <button class="btn" @click="saveDraft">
              <i class="ti ti-device-floppy" /> Save Draft
            </button>
          </div>
        </div>

        <!-- Lab Orders -->
        <div v-show="activeTab === 'lab'" class="tab-panel">
          <div class="section-actions">
            <h3 class="section-title">Lab Orders</h3>
            <button class="btn btn-primary btn-sm" @click="addLabOrder">
              <i class="ti ti-plus" /> Order Test
            </button>
          </div>

          <div v-for="(order, idx) in form.lab_orders" :key="idx" class="lab-order-row">
            <select v-model="order.test" class="field-select">
              <option value="">Select test…</option>
              <option v-for="t in labTests" :key="t" :value="t">{{ t }}</option>
            </select>
            <select v-model="order.priority" class="field-select" style="width:110px">
              <option>Routine</option>
              <option>Urgent</option>
              <option>STAT</option>
            </select>
            <input v-model="order.notes" type="text" class="field-input" placeholder="Clinical notes…" />
            <button class="btn-icon-del" @click="removeLabOrder(idx)"><i class="ti ti-trash" /></button>
          </div>
          <div v-if="!form.lab_orders.length" class="empty-row">No tests ordered yet.</div>

          <!-- Existing Results -->
          <div v-if="visit.lab_results?.length" style="margin-top:20px">
            <h3 class="section-title">Lab Results</h3>
            <div class="lab-results-table">
              <div
                v-for="r in visit.lab_results"
                :key="r.id"
                class="lab-result-row"
                :class="r.flag"
              >
                <div class="lr-test">{{ r.test }}</div>
                <div class="lr-result" :class="r.flag">{{ r.result }} {{ r.unit }}</div>
                <div class="lr-range">Ref: {{ r.reference_range }}</div>
                <span class="badge" :class="`badge-${r.flag}`">{{ r.flag }}</span>
              </div>
            </div>
            <div class="field-group" style="margin-top:10px">
              <label class="field-label">Lab Result Notes</label>
              <textarea v-model="form.lab_notes" rows="2" class="field-textarea" placeholder="Your interpretation of lab results…" />
            </div>
          </div>
        </div>

        <!-- Follow Up -->
        <div v-show="activeTab === 'followup'" class="tab-panel">
          <div class="field-row-2">
            <div class="field-group">
              <label class="field-label">Follow Up Date</label>
              <input v-model="form.followup.date" type="date" class="field-input" />
            </div>
            <div class="field-group">
              <label class="field-label">Type</label>
              <select v-model="form.followup.type" class="field-select">
                <option>BP Check</option>
                <option>Lab Review</option>
                <option>Medication Review</option>
                <option>Post-procedure</option>
                <option>General Follow Up</option>
              </select>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Priority</label>
            <div class="radio-group">
              <label v-for="p in ['Routine', 'Urgent', 'Critical']" :key="p">
                <input v-model="form.followup.priority" type="radio" :value="p" />
                {{ p }}
              </label>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Instructions for Patient</label>
            <textarea v-model="form.followup.instructions" rows="4" class="field-textarea" placeholder="Fast for 8 hrs, bring home BP log, call if symptoms worsen…" />
          </div>
          <div class="field-group">
            <label class="field-label">Internal Note (not shown to patient)</label>
            <textarea v-model="form.followup.internal_note" rows="2" class="field-textarea" placeholder="Monitor HbA1c, check renal function on next visit…" />
          </div>
          <button class="btn btn-primary" @click="saveFollowUp">
            <i class="ti ti-check" /> Save Follow Up
          </button>
        </div>

        <!-- Referrals -->
        <div v-show="activeTab === 'referral'" class="tab-panel">
          <div class="field-row-2">
            <div class="field-group">
              <label class="field-label">Refer To (Specialty)</label>
              <select v-model="form.referral.specialty" class="field-select">
                <option>Cardiology</option>
                <option>Nephrology</option>
                <option>Endocrinology</option>
                <option>Neurology</option>
                <option>Orthopedics</option>
                <option>Pulmonology</option>
                <option>Dermatology</option>
                <option>Ophthalmology</option>
                <option>ENT</option>
                <option>Gynecology</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">Priority</label>
              <select v-model="form.referral.priority" class="field-select">
                <option>Routine</option>
                <option>Urgent</option>
                <option>Emergency</option>
              </select>
            </div>
          </div>
          <div class="field-group">
            <label class="field-label">Reason for Referral <span class="required">*</span></label>
            <textarea v-model="form.referral.reason" rows="4" class="field-textarea" placeholder="Describe the clinical reason for referral…" />
          </div>
          <div class="field-group">
            <label class="field-label">Clinical Summary for Receiving Doctor</label>
            <textarea v-model="form.referral.summary" rows="3" class="field-textarea" placeholder="Brief summary of history, findings, and current management…" />
          </div>
          <button class="btn btn-primary" @click="saveReferral">
            <i class="ti ti-send" /> Send Referral
          </button>
        </div>

        <!-- Bottom CTA -->
        <div class="consult-footer">
          <button class="btn btn-save" :disabled="saving" @click="saveAndComplete">
            <i class="ti ti-check" />
            {{ saving ? 'Saving…' : 'Save & Complete Visit' }}
          </button>
          <Link
            v-if="nextPatient"
            :href="route('doctor.consultations.show', nextPatient.visit_id)"
            class="btn"
          >
            Next Patient: {{ nextPatient.name }} <i class="ti ti-arrow-right" />
          </Link>
          <button v-else class="btn" disabled style="opacity:.4">No more patients</button>
        </div>
      </div>
    </div>
  </DoctorLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
// import { route } from 'ziggy-js'
import DoctorLayout from '@/Layouts/DoctorLayout.vue'
// import VitalBox from '@/Components/Doctor/VitalBox.vue'

const props = defineProps({
  visit: Object,
  nextPatient: Object,
})

const activeTab = ref('subjective')
const showVitalsEdit = ref(false)
const saving = ref(false)
const icdSuggestions = ref([])

const tabs = [
  { key: 'subjective',   label: 'Subjective',   icon: 'ti-user',       done: false },
  { key: 'objective',    label: 'Objective',    icon: 'ti-stethoscope', done: false },
  { key: 'diagnosis',    label: 'Diagnosis',    icon: 'ti-brain',       done: false },
  { key: 'prescription', label: 'Prescription', icon: 'ti-pill',        done: false },
  { key: 'lab',          label: 'Lab Orders',   icon: 'ti-flask',       done: false },
  { key: 'followup',     label: 'Follow Up',    icon: 'ti-repeat',      done: false },
  { key: 'referral',     label: 'Referral',     icon: 'ti-send',        done: false },
]

const vitals = [
  { key: 'bp',     label: 'BP',     unit: 'mmHg', status: 'high'   },
  { key: 'pulse',  label: 'Pulse',  unit: 'bpm',  status: 'normal' },
  { key: 'temp',   label: 'Temp',   unit: '°F',   status: 'normal' },
  { key: 'spo2',   label: 'SpO2',   unit: '%',    status: 'normal' },
  { key: 'weight', label: 'Weight', unit: 'kg',   status: 'normal' },
  { key: 'bmi',    label: 'BMI',    unit: '',     status: 'caution'},
]

const labTests = [
  'CBC', 'LFT', 'KFT', 'FBS', 'HbA1c', 'Lipid Profile',
  'TSH / T3 / T4', 'Urine R/E', 'Urine C/S', 'ECG',
  'Chest X-Ray', 'Uric Acid', 'ESR', 'CRP', 'Serum Electrolytes',
]

const form = reactive({
  vitals: {
    bp:     props.visit?.vitals?.bp     ?? '',
    pulse:  props.visit?.vitals?.pulse  ?? '',
    temp:   props.visit?.vitals?.temp   ?? '',
    spo2:   props.visit?.vitals?.spo2   ?? '',
    weight: props.visit?.vitals?.weight ?? '',
    bmi:    props.visit?.vitals?.bmi    ?? '',
  },
  subjective: {
    chief_complaint: props.visit?.soap?.subjective?.chief_complaint ?? '',
    hpi:             props.visit?.soap?.subjective?.hpi             ?? '',
    ros:             props.visit?.soap?.subjective?.ros             ?? '',
    allergies:       props.visit?.soap?.subjective?.allergies       ?? '',
  },
  objective: {
    general:      props.visit?.soap?.objective?.general      ?? '',
    systems_exam: props.visit?.soap?.objective?.systems_exam ?? '',
    investigations: props.visit?.soap?.objective?.investigations ?? '',
  },
  assessment: {
    primary_diagnosis:    props.visit?.soap?.assessment?.primary_diagnosis    ?? '',
    secondary_diagnoses:  props.visit?.soap?.assessment?.secondary_diagnoses  ?? '',
    clinical_notes:       props.visit?.soap?.assessment?.clinical_notes       ?? '',
  },
  prescription: {
    drugs:        props.visit?.prescription?.drugs        ?? [],
    instructions: props.visit?.prescription?.instructions ?? '',
  },
  lab_orders: props.visit?.lab_orders ?? [],
  lab_notes:  props.visit?.lab_notes  ?? '',
  followup: {
    date:          props.visit?.followup?.date          ?? '',
    type:          props.visit?.followup?.type          ?? 'General Follow Up',
    priority:      props.visit?.followup?.priority      ?? 'Routine',
    instructions:  props.visit?.followup?.instructions  ?? '',
    internal_note: props.visit?.followup?.internal_note ?? '',
  },
  referral: {
    specialty: '',
    priority:  'Routine',
    reason:    '',
    summary:   '',
  },
})

const initials = computed(() => {
  const name = props.visit?.patient?.name || ''
  return name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase()
})

function addDrug() {
  form.prescription.drugs.push({ name: '', dose: '', frequency: 'OD', duration: '', route: 'Oral' })
}
function removeDrug(idx) {
  form.prescription.drugs.splice(idx, 1)
}
function addLabOrder() {
  form.lab_orders.push({ test: '', priority: 'Routine', notes: '' })
}
function removeLabOrder(idx) {
  form.lab_orders.splice(idx, 1)
}

let icdDebounce = null
function searchICD() {
  clearTimeout(icdDebounce)
  icdDebounce = setTimeout(async () => {
    const q = form.assessment.primary_diagnosis
    if (!q || q.length < 3) { icdSuggestions.value = []; return }
    // Hits your Laravel ICD search endpoint
    const res = await fetch(route('doctor.icd.search') + '?q=' + encodeURIComponent(q))
    icdSuggestions.value = await res.json()
  }, 300)
}
function selectICD(item, field) {
  form.assessment.primary_diagnosis = `${item.code} — ${item.label}`
  icdSuggestions.value = []
}

async function saveAndComplete() {
  saving.value = true
  router.put(route('doctor.consultations.update', props.visit.id), form, {
    preserveScroll: true,
    onFinish: () => { saving.value = false },
  })
}

function saveDraft() {
  router.put(route('doctor.consultations.draft', props.visit.id), form, {
    preserveScroll: true,
    preserveState: true,
  })
}

function printPrescription() {
  window.open(route('doctor.consultations.prescription.print', props.visit.id), '_blank')
}

function saveFollowUp() {
  router.post(route('doctor.followups.store'), {
    visit_id: props.visit.id,
    patient_id: props.visit.patient.id,
    ...form.followup,
  }, { preserveScroll: true })
}

function saveReferral() {
  router.post(route('doctor.referrals.store'), {
    visit_id: props.visit.id,
    patient_id: props.visit.patient.id,
    ...form.referral,
  }, { preserveScroll: true })
}

function confirmEndVisit() {
  if (confirm('Mark this visit as complete?')) {
    router.patch(route('doctor.visits.complete', props.visit.id))
  }
}
</script>

<style scoped>
/* ── Header ─────────────────────────────────── */
.consult-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  background: #fff; border: 1px solid #e8eaed; border-radius: 12px;
  padding: 14px 18px; margin-bottom: 14px; gap: 12px;
  flex-wrap: wrap;
}
.patient-identity { display: flex; align-items: center; gap: 12px; }
.token-badge {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700; flex-shrink: 0;
  background: #f3f4f6; color: #6b7280;
}
.token-badge.active { background: #0F6E56; color: #fff; }
.patient-avatar {
  width: 42px; height: 42px; border-radius: 50%;
  background: #E6F1FB; color: #185FA5;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700; flex-shrink: 0;
}
.patient-name { font-size: 15px; font-weight: 700; color: #111827; }
.patient-meta { font-size: 12px; font-weight: 400; color: #6b7280; }
.patient-conditions { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 5px; }

.header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* ── Layout ─────────────────────────────────── */
.consult-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 14px;
  align-items: start;
}
@media (max-width: 960px) { .consult-layout { grid-template-columns: 1fr; } }

.consult-left { display: flex; flex-direction: column; gap: 10px; }

/* ── Panels ─────────────────────────────────── */
.panel {
  background: #fff; border: 1px solid #e8eaed; border-radius: 10px; padding: 12px 14px;
}
.panel-header {
  display: flex; align-items: center; justify-content: space-between;
  font-size: 11px; font-weight: 700; color: #6b7280;
  text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px;
}
.panel-divider { border: none; border-top: 1px solid #f3f4f6; margin: 8px 0; }

/* ── Vitals ─────────────────────────────────── */
.vitals-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }

/* ── Info Table ─────────────────────────────────── */
.info-table {}
.info-row {
  display: flex; justify-content: space-between;
  align-items: baseline; padding: 3px 0; font-size: 12px;
  border-bottom: 1px solid #f9fafb;
}
.info-row:last-child { border-bottom: none; }
.info-row span:first-child { color: #6b7280; }
.info-row span:last-child { color: #111827; font-weight: 500; }

.history-chips { display: flex; flex-wrap: wrap; gap: 4px; }
.med-row { font-size: 12px; color: #374151; padding: 3px 0; display: flex; align-items: center; gap: 5px; }
.text-muted { font-size: 11px; color: #9ca3af; }
.card-link { font-size: 11px; color: #0F6E56; text-decoration: none; font-weight: 400; text-transform: none; letter-spacing: 0; }

.prev-visit-row { padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
.prev-visit-row:last-child { border-bottom: none; }
.pv-date { font-size: 10px; color: #9ca3af; }
.pv-diag { font-size: 12px; color: #374151; font-weight: 500; }

/* ── SOAP Tabs ─────────────────────────────────── */
.consult-main { display: flex; flex-direction: column; gap: 0; }
.soap-tabs {
  display: flex; gap: 0; overflow-x: auto;
  background: #fff; border: 1px solid #e8eaed;
  border-radius: 10px 10px 0 0; border-bottom: none;
  padding: 0 4px;
}
.soap-tab {
  display: flex; align-items: center; gap: 5px;
  padding: 10px 14px; font-size: 12px; font-weight: 500;
  color: #6b7280; background: none; border: none;
  border-bottom: 2px solid transparent; cursor: pointer;
  white-space: nowrap; transition: all .15s; font-family: inherit;
}
.soap-tab:hover { color: #111827; }
.soap-tab.active { color: #0F6E56; border-bottom-color: #0F6E56; }
.soap-tab.done { color: #3B6D11; }
.tab-done-icon { font-size: 12px; color: #22c55e; }

.tab-panel {
  background: #fff; border: 1px solid #e8eaed;
  border-radius: 0 0 10px 10px;
  padding: 16px; display: flex; flex-direction: column; gap: 12px;
}

/* ── Fields ─────────────────────────────────── */
.field-group { display: flex; flex-direction: column; gap: 4px; }
.field-label { font-size: 12px; font-weight: 600; color: #374151; }
.required { color: #E24B4A; }
.field-textarea {
  border: 1px solid #d1d5db; border-radius: 8px;
  padding: 8px 10px; font-size: 13px; font-family: inherit;
  background: #fff; color: #111827; resize: vertical;
  transition: border-color .15s;
}
.field-textarea:focus { outline: none; border-color: #0F6E56; }
.field-input {
  border: 1px solid #d1d5db; border-radius: 8px;
  padding: 7px 10px; font-size: 13px; font-family: inherit;
  background: #fff; color: #111827; width: 100%;
}
.field-input:focus { outline: none; border-color: #0F6E56; }
.field-select {
  border: 1px solid #d1d5db; border-radius: 8px;
  padding: 7px 10px; font-size: 13px; font-family: inherit;
  background: #fff; color: #111827; width: 100%;
}
.field-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* ── ICD Search ─────────────────────────────────── */
.icd-search-wrap { position: relative; }
.icd-dropdown {
  position: absolute; top: 100%; left: 0; right: 0; z-index: 50;
  background: #fff; border: 1px solid #d1d5db; border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,.1); max-height: 200px; overflow-y: auto;
}
.icd-item { padding: 8px 12px; font-size: 12.5px; cursor: pointer; }
.icd-item:hover { background: #f0fdf9; }

/* ── Rx Table ─────────────────────────────────── */
.rx-table-wrap { overflow-x: auto; border: 1px solid #e8eaed; border-radius: 8px; }
.rx-table { width: 100%; border-collapse: collapse; }
.rx-table th {
  font-size: 11px; font-weight: 600; color: #6b7280;
  text-transform: uppercase; letter-spacing: .04em;
  text-align: left; padding: 8px 10px;
  background: #f9fafb; border-bottom: 1px solid #e8eaed;
}
.rx-table td { padding: 6px 6px; border-bottom: 1px solid #f3f4f6; }
.rx-table tr:last-child td { border-bottom: none; }
.table-input {
  border: 1px solid #e5e7eb; border-radius: 6px;
  padding: 5px 8px; font-size: 12px; font-family: inherit;
  width: 100%; min-width: 60px;
}
.table-input:focus { outline: none; border-color: #0F6E56; }
.table-select {
  border: 1px solid #e5e7eb; border-radius: 6px;
  padding: 5px 6px; font-size: 12px; font-family: inherit;
  width: 100%;
}
.empty-row { text-align: center; color: #9ca3af; font-size: 12px; padding: 16px; }

/* ── Lab ─────────────────────────────────── */
.lab-order-row {
  display: flex; gap: 8px; align-items: center;
  padding: 6px 0; border-bottom: 1px solid #f3f4f6;
}
.lab-results-table { display: flex; flex-direction: column; }
.lab-result-row {
  display: flex; align-items: center; gap: 10px;
  padding: 7px 0; border-bottom: 1px solid #f3f4f6; font-size: 12px;
}
.lr-test { flex: 1; font-weight: 600; color: #111827; }
.lr-result { font-weight: 700; }
.lr-result.high { color: #E24B4A; }
.lr-result.normal { color: #3B6D11; }
.lr-result.low { color: #BA7517; }
.lr-range { font-size: 11px; color: #9ca3af; }

/* ── Radio Group ─────────────────────────────────── */
.radio-group { display: flex; gap: 16px; font-size: 13px; color: #374151; }
.radio-group label { display: flex; align-items: center; gap: 5px; cursor: pointer; }

/* ── Section Actions ─────────────────────────────────── */
.section-actions { display: flex; align-items: center; justify-content: space-between; }
.section-title { font-size: 13px; font-weight: 600; color: #111827; }

/* ── Footer ─────────────────────────────────── */
.consult-footer {
  display: flex; gap: 10px; margin-top: 14px; align-items: center;
}
.rx-footer { display: flex; gap: 10px; margin-top: 8px; }
.btn-save {
  flex: 1; display: flex; align-items: center; justify-content: center;
  gap: 6px; padding: 10px 20px; background: #0F6E56; color: #fff;
  border: none; border-radius: 8px; font-size: 13.5px; font-weight: 600;
  cursor: pointer; font-family: inherit; transition: background .15s;
}
.btn-save:hover { background: #1D9E75; }
.btn-save:disabled { opacity: .6; cursor: default; }
.btn-icon-del {
  background: none; border: 1px solid #fee2e2; border-radius: 6px;
  color: #E24B4A; cursor: pointer; padding: 4px 6px; font-size: 13px;
}
.btn-icon-del:hover { background: #fee2e2; }

/* ── Buttons ─────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 14px; border-radius: 8px; font-size: 12.5px;
  font-family: inherit; cursor: pointer;
  border: 1px solid #e5e7eb; background: #fff; color: #374151;
  text-decoration: none; transition: all .15s;
}
.btn:hover { background: #f9fafb; }
.btn-primary { background: #0F6E56; color: #fff; border-color: #0F6E56; }
.btn-primary:hover { background: #1D9E75; border-color: #1D9E75; }
.btn-sm { padding: 5px 10px; font-size: 12px; }
.btn-xs { padding: 2px 6px; font-size: 11px; }
.btn-danger { background: #FAECE7; color: #993C1D; border-color: #D85A30; }
.btn-danger:hover { background: #f5c4b3; }

/* ── Badges ─────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center; gap: 3px;
  padding: 2px 8px; border-radius: 20px; font-size: 10.5px; font-weight: 600;
}
.badge-active   { background: #E1F5EE; color: #0F6E56; }
.badge-condition { background: #E6F1FB; color: #185FA5; }
.badge-allergy  { background: #FAECE7; color: #993C1D; }
.badge-history  { background: #f3f4f6; color: #374151; }
.badge-high     { background: #FAECE7; color: #993C1D; }
.badge-normal   { background: #EAF3DE; color: #3B6D11; }
.badge-low      { background: #FAEEDA; color: #BA7517; }
.badge-pending  { background: #FAEEDA; color: #BA7517; }
</style>