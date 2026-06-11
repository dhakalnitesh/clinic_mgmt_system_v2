<template>
  <AuthenticatedLayout :title="`Return — ${returnData.return_number}`">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.supplier-returns.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900 font-mono tracking-tight">{{ returnData.return_number }}</h1>
            <StatusBadge :status="returnData.status" type="supplier_return" />
          </div>
          <p class="text-sm text-slate-500 mt-0.5">
            {{ returnData.supplier?.name }} · {{ formatDate(returnData.return_date) }}
            · Returned by: {{ returnData.returned_by }}
          </p>
        </div>
      </div>

      <button v-if="returnData.status === 'draft'" @click="complete"
              class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 transition">
        <CheckBadgeIcon class="w-4 h-4" />
        Complete Return
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Info -->
      <div class="space-y-5">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Details</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Return No."  :value="returnData.return_number" mono />
            <InfoRow label="Date"        :value="formatDate(returnData.return_date)" />
            <InfoRow label="Reason"      :value="returnData.reason?.replace(/_/g,' ')" />
            <InfoRow label="Supplier"    :value="returnData.supplier?.name" />
            <InfoRow label="Returned By" :value="returnData.returned_by" />
            <div class="border-t border-slate-100 pt-3">
              <div class="flex justify-between font-bold text-slate-900">
                <dt>Total</dt>
                <dd class="font-mono">{{ formatCurrency(returnData.total_amount) }}</dd>
              </div>
            </div>
          </dl>
        </div>
        <div v-if="returnData.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-1">Notes</p>
          <p class="text-sm text-amber-800">{{ returnData.notes }}</p>
        </div>
      </div>

      <!-- Items -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200">
            <h3 class="font-semibold text-slate-800">Return Items</h3>
          </div>
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50/80">
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Qty</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Price</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in returnData.items" :key="item.id" class="hover:bg-slate-50/60">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                  <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                    <FormBadge v-if="item.form" :form="item.form" size="xs" />
                    <span>{{ item.strength }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="font-mono text-xs text-slate-700">{{ item.batch_number }}</div>
                  <div class="text-xs text-slate-400">Exp: {{ formatDate(item.expiry_date) }}</div>
                </td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ item.quantity }}</td>
                <td class="px-4 py-3 text-right font-mono text-xs text-slate-600">{{ formatCurrency(item.unit_price) }}</td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(item.subtotal) }}</td>
              </tr>
            </tbody>
            <tfoot class="border-t-2 border-slate-200 bg-slate-50">
              <tr>
                <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Total</td>
                <td class="px-4 py-3 text-right font-mono font-bold text-lg text-teal-700">
                  {{ formatCurrency(returnData.total_amount) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ArrowLeftIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout   from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Pharmacy/StatusBadge.vue'
import FormBadge   from '@/Components/Pharmacy/FormBadge.vue'

const InfoRow = {
  props: ['label', 'value', 'mono'],
  template: `<div class="flex justify-between gap-4">
    <dt class="text-slate-400 shrink-0">{{ label }}</dt>
    <dd class="text-right text-slate-700 font-medium" :class="mono ? 'font-mono text-xs' : ''">{{ value ?? '—' }}</dd>
  </div>`,
}

const props = defineProps({ return: Object })
const returnData = props.return

function complete() {
  router.patch(route('pharmacy.supplier-returns.complete', returnData.id))
}
function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>