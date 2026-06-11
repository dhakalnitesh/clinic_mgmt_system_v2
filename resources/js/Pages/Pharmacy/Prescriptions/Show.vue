<template>
  <AuthenticatedLayout

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.prescriptions.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-slate-900 font-mono tracking-tight">
              {{ prescription.prescription_number }}
            </h1>
            <span :class="statusClass" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold">
              {{ capitalize(prescription.status) }}
            </span>
          </div>
          <p class="text-sm text-slate-500 mt-0.5">
            {{ formatDate(prescription.prescription_date) }}
            <span v-if="prescription.patient_id"> · Patient #{{ prescription.patient_id }}</span>
            <span v-if="prescription.doctor_id"> · Doctor #{{ prescription.doctor_id }}</span>
          </p>
        </div>
      </div>

      <!-- Dispense button -->
      <Link v-if="['pending','partial'].includes(prescription.status)"
            :href="route('pharmacy.sales.create') + '?prescription_id=' + prescription.id"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <BeakerIcon class="w-4 h-4" />
        Dispense Medicines
      </Link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: Prescription Info -->
      <div class="space-y-5">

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Prescription Info</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Rx Number"    :value="prescription.prescription_number" mono />
            <InfoRow label="Date"         :value="formatDate(prescription.prescription_date)" />
            <InfoRow label="Patient"      :value="prescription.patient_id ? `#${prescription.patient_id}` : '—'" mono />
            <InfoRow label="Doctor"       :value="prescription.doctor_id  ? `#${prescription.doctor_id}`  : '—'" mono />
            <InfoRow label="Status"       :value="capitalize(prescription.status)" />
            <div v-if="prescription.dispensed_by" class="border-t border-slate-100 pt-3 space-y-3">
              <InfoRow label="Dispensed By" :value="prescription.dispensed_by" />
              <InfoRow label="Dispensed At" :value="prescription.dispensed_at" />
            </div>
          </dl>
        </div>

        <!-- Progress -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Dispensing Progress</h3>
          <div class="space-y-3">
            <div v-for="item in prescription.items" :key="item.id">
              <div class="flex items-center justify-between text-xs mb-1">
                <span class="font-medium text-slate-700 truncate max-w-32">{{ item.medicine_name }}</span>
                <span class="font-mono text-slate-500 shrink-0 ml-2">
                  {{ item.quantity_dispensed }}/{{ item.quantity_prescribed }}
                </span>
              </div>
              <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all"
                     :class="item.status === 'dispensed' ? 'bg-emerald-500' : 'bg-teal-500'"
                     :style="{ width: `${Math.min(100, (item.quantity_dispensed / item.quantity_prescribed) * 100)}%` }" />
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="prescription.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Notes</h3>
          <p class="text-sm text-amber-800 leading-relaxed">{{ prescription.notes }}</p>
        </div>

      </div>

      <!-- Right: Items Table -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Prescribed Items</h3>
            <span class="text-xs text-slate-400">{{ prescription.items?.length }} items</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine / Generic</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Dosage</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Prescribed</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Dispensed</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Pending</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in prescription.items" :key="item.id"
                    class="hover:bg-slate-50/60 transition-colors"
                    :class="item.status === 'dispensed' ? 'opacity-60' : ''">

                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                    <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                      <FormBadge v-if="item.form" :form="item.form" size="xs" />
                      <span>{{ item.strength }}</span>
                      <span v-if="item.is_substitutable" class="text-blue-500 font-medium">· Sub OK</span>
                    </div>
                  </td>

                  <td class="px-4 py-3">
                    <p class="text-slate-700 text-xs">{{ item.dosage_instruction ?? '—' }}</p>
                    <p v-if="item.frequency" class="text-xs text-slate-400 mt-0.5">
                      {{ item.frequency?.replace(/_/g, ' ') }}
                      <span v-if="item.duration_days"> · {{ item.duration_days }}d</span>
                    </p>
                  </td>

                  <td class="px-4 py-3 text-right font-mono font-semibold text-slate-800">
                    {{ item.quantity_prescribed }}
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-emerald-600 font-semibold">
                    {{ item.quantity_dispensed }}
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-bold"
                      :class="item.pending_quantity > 0 ? 'text-amber-600' : 'text-slate-300'">
                    {{ item.pending_quantity }}
                  </td>

                  <td class="px-4 py-3">
                    <span :class="{
                      'bg-amber-50 text-amber-700 ring-1 ring-amber-200':   item.status === 'pending',
                      'bg-orange-50 text-orange-700 ring-1 ring-orange-200': item.status === 'partial',
                      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': item.status === 'dispensed',
                    }" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
                      {{ capitalize(item.status) }}
                    </span>
                  </td>

                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link }     from '@inertiajs/vue3'
import { ArrowLeftIcon, BeakerIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormBadge from '@/Components/Pharmacy/FormBadge.vue'

const InfoRow = {
  props: ['label', 'value', 'mono'],
  template: `
    <div class="flex items-start justify-between gap-4">
      <dt class="text-slate-400 shrink-0">{{ label }}</dt>
      <dd class="text-right text-slate-700 font-medium" :class="mono ? 'font-mono text-xs' : ''">
        {{ value ?? '—' }}
      </dd>
    </div>`,
}

const props = defineProps({ prescription: Object })

const statusClass = computed(() => ({
  pending:   'bg-amber-50  text-amber-700  ring-1 ring-amber-200',
  partial:   'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
  dispensed: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  cancelled: 'bg-red-50    text-red-600    ring-1 ring-red-200',
}[props.prescription.status] ?? 'bg-slate-100 text-slate-500'))

function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>