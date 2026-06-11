<template>
  <AuthenticatedLayout>

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.purchase-orders.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-slate-900 font-mono tracking-tight">{{ grn.grn_number }}</h1>
            <StatusBadge :status="grn.status" type="grn" />
          </div>
          <p class="text-sm text-slate-500 mt-0.5">
            {{ grn.supplier?.name }} · Received {{ formatDate(grn.received_date) }}
            <span v-if="grn.purchase_order"> · PO:
              <Link :href="route('pharmacy.purchase-orders.show', grn.purchase_order.id)"
                    class="text-teal-600 hover:underline font-medium">
                {{ grn.purchase_order.po_number }}
              </Link>
            </span>
          </p>
        </div>
      </div>

      <!-- Status-sensitive Actions -->
      <div class="flex items-center gap-2 shrink-0 flex-wrap">

        <!-- Pending → can verify -->
        <button v-if="grn.status === 'pending'"
                @click="verifyGrn"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 transition">
          <CheckBadgeIcon class="w-4 h-4" />
          Verify GRN
        </button>

        <!-- Verified → can post (creates stock batches) -->
        <button v-if="grn.status === 'verified'"
                @click="postGrn"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 transition">
          <ArrowUpTrayIcon class="w-4 h-4" />
          Post to Inventory
        </button>

        <!-- Posted — show success indicator -->
        <div v-if="grn.status === 'posted'"
             class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg">
          <CheckCircleIcon class="w-4 h-4" />
          Posted — Stock Updated
        </div>

        <!-- Delete (pending only) -->
        <button v-if="grn.status === 'pending'"
                @click="deleteGrn"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition">
          Delete
        </button>

      </div>
    </div>

    <!-- Workflow progress bar -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6">
      <div class="flex items-center justify-between">
        <WorkflowStep label="Created"   :done="true"                    :active="grn.status === 'pending'" />
        <WorkflowArrow />
        <WorkflowStep label="Verified"  :done="['verified','posted'].includes(grn.status)"
                                        :active="grn.status === 'verified'" />
        <WorkflowArrow />
        <WorkflowStep label="Posted"    :done="grn.status === 'posted'" :active="grn.status === 'posted'" />
      </div>
      <p class="mt-3 text-xs text-center text-slate-500">
        <template v-if="grn.status === 'pending'">
          ⓘ Review the items below, then click <strong>Verify GRN</strong> to confirm quantities are correct.
        </template>
        <template v-else-if="grn.status === 'verified'">
          ⓘ GRN verified by {{ grn.verified_by }}. Click <strong>Post to Inventory</strong> to create stock batches.
        </template>
        <template v-else>
          ✅ GRN posted. Stock batches created and inventory updated.
        </template>
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: GRN Info -->
      <div class="space-y-5">

        <!-- Receipt Info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Receipt Details</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="GRN No."        :value="grn.grn_number" mono />
            <InfoRow label="Received Date"  :value="formatDate(grn.received_date)" />
            <InfoRow label="Invoice No."    :value="grn.invoice_number" mono />
            <InfoRow label="Invoice Date"   :value="grn.invoice_date ? formatDate(grn.invoice_date) : '—'" />
            <InfoRow label="Received By"    :value="grn.received_by" />
            <InfoRow label="Verified By"    :value="grn.verified_by ?? '—'" />
          </dl>
        </div>

        <!-- Supplier -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Supplier</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Name"    :value="grn.supplier?.name" />
            <InfoRow label="Phone"   :value="grn.supplier?.phone" />
            <InfoRow label="Contact" :value="grn.supplier?.contact_person" />
          </dl>
        </div>

        <!-- Totals -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Totals</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between text-slate-600">
              <dt>Subtotal</dt>
              <dd class="font-mono">{{ formatCurrency(grn.subtotal) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Discount</dt>
              <dd class="font-mono text-red-600">- {{ formatCurrency(grn.discount_amount) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Tax</dt>
              <dd class="font-mono">{{ formatCurrency(grn.tax_amount) }}</dd>
            </div>
            <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-2">
              <dt>Total</dt>
              <dd class="font-mono text-lg">{{ formatCurrency(grn.total_amount) }}</dd>
            </div>
          </dl>
        </div>

        <div v-if="grn.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Notes</h3>
          <p class="text-sm text-amber-800">{{ grn.notes }}</p>
        </div>

      </div>

      <!-- Right: Items Table -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Items Received</h3>
            <span class="text-xs text-slate-400">{{ grn.items?.length }} items</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Expiry</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Rcvd</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Free</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Cost</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Sale</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Subtotal</th>
                  <th v-if="grn.status === 'posted'" class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch ID</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in grn.items" :key="item.id"
                    class="hover:bg-slate-50/60 transition-colors">

                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                    <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                      <FormBadge :form="item.form" size="xs" />
                      <span>{{ item.strength }}</span>
                    </div>
                  </td>

                  <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ item.batch_number }}</td>

                  <td class="px-4 py-3">
                    <ExpiryTag
                      :date="item.expiry_date"
                      :status="expiryStatus(item.expiry_date)"
                      :days="daysToExpiry(item.expiry_date)"
                      :show-days="false"
                    />
                  </td>

                  <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ item.quantity_received }}</td>
                  <td class="px-4 py-3 text-right font-mono text-emerald-600">{{ item.free_quantity || 0 }}</td>
                  <td class="px-4 py-3 text-right font-mono text-xs text-slate-500">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-slate-700">{{ formatCurrency(item.sale_price) }}</td>
                  <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(item.subtotal) }}</td>

                  <td v-if="grn.status === 'posted'" class="px-4 py-3">
                    <Link v-if="item.stock_batch_id"
                          :href="route('pharmacy.inventory.batch', item.stock_batch_id)"
                          class="inline-flex items-center gap-1 text-xs text-teal-600 hover:underline font-mono">
                      #{{ item.stock_batch_id }}
                      <ArrowTopRightOnSquareIcon class="w-3 h-3" />
                    </Link>
                  </td>

                </tr>
              </tbody>
              <tfoot class="border-t-2 border-slate-200 bg-slate-50/60">
                <tr>
                  <td colspan="7" class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Grand Total</td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 text-base">{{ formatCurrency(grn.total_amount) }}</td>
                  <td v-if="grn.status === 'posted'"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, CheckBadgeIcon, ArrowUpTrayIcon,
  CheckCircleIcon, ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout   from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Pharmacy/StatusBadge.vue'
import FormBadge   from '@/Components/Pharmacy/FormBadge.vue'
import ExpiryTag   from '@/Components/Pharmacy/ExpiryTag.vue'

// ── Inline sub-components ──────────────────────────────────────────
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

const WorkflowStep = {
  props: ['label', 'done', 'active'],
  template: `
    <div class="flex flex-col items-center gap-1.5">
      <div :class="[
        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors',
        done && !active ? 'bg-emerald-500 text-white' :
        active ? 'bg-teal-600 text-white ring-4 ring-teal-100' :
        'bg-slate-200 text-slate-400',
      ]">
        <svg v-if="done && !active" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <span v-else class="text-xs">{{ label[0] }}</span>
      </div>
      <span :class="['text-xs font-medium', active ? 'text-teal-700' : done ? 'text-emerald-600' : 'text-slate-400']">
        {{ label }}
      </span>
    </div>`,
}

const WorkflowArrow = {
  template: `<div class="flex-1 h-px bg-slate-200 mx-2 mt-[-10px]" />`,
}

const props = defineProps({ grn: Object })

function verifyGrn() { router.patch(route('pharmacy.grn.verify', props.grn.id)) }
function postGrn()   { router.patch(route('pharmacy.grn.post',   props.grn.id)) }
function deleteGrn() { router.delete(route('pharmacy.grn.destroy', props.grn.id)) }

function expiryStatus(date) {
  if (!date) return 'good'
  const days = daysToExpiry(date)
  if (days < 0)   return 'expired'
  if (days <= 30) return 'critical'
  if (days <= 90) return 'warning'
  return 'good'
}
function daysToExpiry(date) {
  return Math.ceil((new Date(date) - new Date()) / 86400000)
}
function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>