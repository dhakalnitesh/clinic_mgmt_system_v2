<template>
  <AuthenticatedLayout :title="`Batch — ${batch.batch_number}`">

    <!-- ── Header ─────────────────────────────────────────────────── -->
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.inventory.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <div class="flex items-center gap-3">
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight font-mono">
            {{ batch.batch_number }}
          </h1>
          <ExpiryTag :date="batch.expiry_date" :status="batch.expiry_status" :days="batch.days_to_expiry" />
        </div>
        <p class="text-sm text-slate-500 mt-0.5">
          <Link :href="route('pharmacy.medicines.show', batch.medicine_id)"
                class="text-teal-600 hover:underline font-medium">
            {{ batch.medicine?.name }}
          </Link>
          <span v-if="batch.medicine?.strength" class="ml-1 text-slate-400">· {{ batch.medicine.strength }}</span>
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- ── Left: Batch Info ───────────────────────────────────────── -->
      <div class="lg:col-span-1 space-y-5">

        <!-- Quantity Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Stock Quantities</h3>

          <div class="space-y-3">
            <QuantityRow label="Received"  :value="batch.quantity_received" color="slate" />
            <QuantityRow label="Sold"      :value="batch.quantity_sold"     color="teal" />
            <QuantityRow label="Adjusted"  :value="batch.quantity_adjusted" color="amber" />
            <div class="border-t border-slate-200 pt-3">
              <QuantityRow label="Available" :value="batch.quantity_available"
                           :color="batch.quantity_available <= 0 ? 'red' : 'emerald'"
                           :large="true" />
            </div>
          </div>

          <!-- Stock bar -->
          <div class="mt-4">
            <div class="flex justify-between text-xs text-slate-400 mb-1">
              <span>Consumed</span>
              <span>{{ consumedPercent }}%</span>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
              <div class="h-full bg-teal-500 rounded-full transition-all"
                   :style="{ width: `${consumedPercent}%` }" />
            </div>
          </div>
        </div>

        <!-- Batch Info Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Batch Details</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Batch No."    :value="batch.batch_number" mono />
            <InfoRow label="Mfg Date"     :value="formatDate(batch.manufacturing_date)" />
            <InfoRow label="Expiry Date"  :value="formatDate(batch.expiry_date)" />
            <InfoRow label="Purchase Price" :value="formatCurrency(batch.purchase_price)" mono />
            <InfoRow label="Sale Price"   :value="formatCurrency(batch.sale_price)" mono />
            <InfoRow label="MRP"          :value="batch.mrp ? formatCurrency(batch.mrp) : '—'" mono />
            <InfoRow label="Supplier"     :value="batch.supplier?.name ?? '—'" />
            <InfoRow v-if="batch.grn"
                     label="GRN No."
                     :value="batch.grn.grn_number"
                     mono />
          </dl>
        </div>

        <!-- Medicine Info Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Medicine Info</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Brand Name"  :value="batch.medicine?.name" />
            <InfoRow label="Generic"     :value="batch.medicine?.generic?.name ?? '—'" />
            <InfoRow label="Form"        :value="capitalize(batch.medicine?.form ?? '')" />
            <InfoRow label="Strength"    :value="batch.medicine?.strength ?? '—'" />
            <InfoRow label="Category"    :value="batch.medicine?.category?.name ?? '—'" />
            <InfoRow label="Shelf"       :value="batch.medicine?.shelf_location ?? '—'" mono />
          </dl>
        </div>

      </div>

      <!-- ── Right: Transaction Ledger ─────────────────────────────── -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Transaction History</h3>
            <span class="text-xs text-slate-400">{{ batch.transactions?.length ?? 0 }} transactions</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Type</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Reference</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Qty</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <!-- Opening entry -->
                <tr class="bg-emerald-50/40">
                  <td class="px-5 py-3 text-slate-600">{{ formatDate(batch.created_at) }}</td>
                  <td class="px-5 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                      ↑ GRN Receipt
                    </span>
                  </td>
                  <td class="px-5 py-3 font-mono text-xs text-slate-500">
                    {{ batch.grn?.grn_number ?? 'Opening Stock' }}
                  </td>
                  <td class="px-5 py-3 text-right font-mono font-bold text-emerald-700">
                    +{{ batch.quantity_received }}
                  </td>
                </tr>

                <!-- Sales transactions -->
                <template v-if="batch.transactions?.length">
                  <tr v-for="tx in batch.transactions" :key="tx.invoice" class="hover:bg-slate-50/60">
                    <td class="px-5 py-3 text-slate-600">{{ formatDate(tx.date) }}</td>
                    <td class="px-5 py-3">
                      <span :class="txBadgeClass(tx.type)">
                        {{ txLabel(tx.type) }}
                      </span>
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-500">
                      <Link v-if="tx.invoice"
                            :href="route('pharmacy.sales.show', tx.sale_id)"
                            class="text-teal-600 hover:underline">
                        {{ tx.invoice }}
                      </Link>
                      <span v-else>—</span>
                    </td>
                    <td class="px-5 py-3 text-right font-mono font-bold"
                        :class="tx.quantity > 0 ? 'text-emerald-600' : 'text-red-600'">
                      {{ tx.quantity > 0 ? '+' : '' }}{{ tx.quantity }}
                    </td>
                  </tr>
                </template>

                <!-- Empty -->
                <tr v-if="!batch.transactions?.length">
                  <td colspan="4" class="px-5 py-10 text-center text-slate-400 text-sm">
                    No transactions yet — this batch has not been dispensed
                  </td>
                </tr>

              </tbody>
            </table>
          </div>

          <!-- Running balance footer -->
          <div class="border-t border-slate-200 bg-slate-50/60 px-5 py-3 flex justify-end items-center gap-8 text-sm">
            <div class="text-slate-500">
              Total sold: <span class="font-mono font-semibold text-slate-700">{{ batch.quantity_sold }}</span>
            </div>
            <div class="text-slate-500">
              Remaining: <span class="font-mono font-bold text-slate-900">{{ batch.quantity_available }}</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link }     from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ExpiryTag from '@/Components/Pharmacy/ExpiryTag.vue'

// ── Sub-components (inline for simplicity) ─────────────────────────
const QuantityRow = {
  props: ['label', 'value', 'color', 'large'],
  template: `
    <div class="flex items-center justify-between">
      <span class="text-slate-500" :class="large ? 'font-semibold' : ''">{{ label }}</span>
      <span class="font-mono font-semibold" :class="[
        large ? 'text-lg' : 'text-sm',
        color === 'emerald' ? 'text-emerald-600' :
        color === 'red'     ? 'text-red-600' :
        color === 'teal'    ? 'text-teal-700' :
        color === 'amber'   ? 'text-amber-600' :
        'text-slate-700',
      ]">{{ value?.toLocaleString() }}</span>
    </div>
  `,
}

const InfoRow = {
  props: ['label', 'value', 'mono'],
  template: `
    <div class="flex items-start justify-between gap-4">
      <dt class="text-slate-400 shrink-0">{{ label }}</dt>
      <dd class="text-right text-slate-700 font-medium" :class="mono ? 'font-mono' : ''">{{ value }}</dd>
    </div>
  `,
}

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({ batch: Object })

// ── Computed ───────────────────────────────────────────────────────
const consumedPercent = computed(() => {
  if (!props.batch.quantity_received) return 0
  return Math.round((props.batch.quantity_sold / props.batch.quantity_received) * 100)
})

// ── Methods ────────────────────────────────────────────────────────
function txBadgeClass(type) {
  return {
    sale:   'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-600',
    return: 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700',
    adjust: 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700',
  }[type] ?? ''
}

function txLabel(type) {
  return { sale: '↓ Sale', return: '↑ Return', adjust: '⚡ Adjustment' }[type] ?? type
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatCurrency(val) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(val ?? 0)
}

function capitalize(str) {
  return str ? str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ') : ''
}
</script>