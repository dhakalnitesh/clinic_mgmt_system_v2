<template>
  <AuthenticatedLayout :title="order.po_number">

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.purchase-orders.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-slate-900 font-mono tracking-tight">{{ order.po_number }}</h1>
            <StatusBadge :status="order.status" type="po" />
          </div>
          <p class="text-sm text-slate-500 mt-0.5">
            {{ order.supplier?.name }} · Ordered {{ formatDate(order.order_date) }}
            <span v-if="order.ordered_by"> by {{ order.ordered_by }}</span>
          </p>
        </div>
      </div>

      <!-- Action Buttons (context-sensitive by status) -->
      <div class="flex items-center gap-2 shrink-0 flex-wrap">
        <!-- Draft actions -->
        <template v-if="order.status === 'draft'">
          <Link :href="route('pharmacy.purchase-orders.edit', order.id)"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm transition">
            <PencilIcon class="w-4 h-4" />
            Edit
          </Link>
          <button @click="sendPo"
                  class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 transition">
            <PaperAirplaneIcon class="w-4 h-4" />
            Mark as Sent
          </button>
        </template>

        <!-- Sent / Partial actions -->
        <template v-if="['sent','partial'].includes(order.status)">
          <Link :href="route('pharmacy.grn.create') + '?purchase_order_id=' + order.id"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 transition">
            <InboxArrowDownIcon class="w-4 h-4" />
            Receive Goods (GRN)
          </Link>
        </template>

        <!-- Cancel (draft/sent) -->
        <button v-if="['draft','sent'].includes(order.status)"
                @click="cancelPo"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition">
          Cancel Order
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: PO Summary -->
      <div class="space-y-5">

        <!-- Order Info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Order Info</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="PO Number"      :value="order.po_number" mono />
            <InfoRow label="Order Date"     :value="formatDate(order.order_date)" />
            <InfoRow label="Expected"
                     :value="order.expected_delivery_date ? formatDate(order.expected_delivery_date) : '—'" />
            <InfoRow label="Ordered By"     :value="order.ordered_by" />
            <InfoRow v-if="order.approved_by" label="Approved By" :value="order.approved_by" />
          </dl>
        </div>

        <!-- Supplier Info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Supplier</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Name"    :value="order.supplier?.name" />
            <InfoRow label="Phone"   :value="order.supplier?.phone" />
            <InfoRow label="Contact" :value="order.supplier?.contact_person" />
          </dl>
          <Link :href="route('pharmacy.suppliers.show', order.supplier?.id)"
                class="mt-3 block text-xs text-teal-600 hover:underline">
            View supplier profile →
          </Link>
        </div>

        <!-- Totals -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Order Totals</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between text-slate-600">
              <dt>Subtotal</dt>
              <dd class="font-mono">{{ formatCurrency(order.subtotal) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Discount</dt>
              <dd class="font-mono text-red-600">- {{ formatCurrency(order.discount_amount) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Tax</dt>
              <dd class="font-mono">{{ formatCurrency(order.tax_amount) }}</dd>
            </div>
            <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-2">
              <dt>Total</dt>
              <dd class="font-mono text-lg">{{ formatCurrency(order.total_amount) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Notes -->
        <div v-if="order.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Notes</h3>
          <p class="text-sm text-amber-800 leading-relaxed">{{ order.notes }}</p>
        </div>

      </div>

      <!-- Right: Items + GRNs -->
      <div class="lg:col-span-2 space-y-5">

        <!-- Line Items -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Order Items</h3>
            <span class="text-xs text-slate-400">{{ order.items?.length }} items</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Ordered</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Received</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Pending</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Unit Price</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in order.items" :key="item.id"
                    class="hover:bg-slate-50/60 transition-colors">
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                    <div class="text-xs text-slate-400 flex items-center gap-1.5 mt-0.5">
                      <FormBadge :form="item.form" size="xs" />
                      <span>{{ item.strength }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-slate-700">{{ item.quantity_ordered }}</td>
                  <td class="px-4 py-3 text-right font-mono text-emerald-600 font-semibold">{{ item.quantity_received }}</td>
                  <td class="px-4 py-3 text-right">
                    <span :class="[
                      'font-mono font-bold',
                      item.pending_quantity > 0 ? 'text-amber-600' : 'text-slate-400',
                    ]">{{ item.pending_quantity }}</span>
                  </td>
                  <td class="px-4 py-3 text-right font-mono text-slate-600">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(item.subtotal) }}</td>
                </tr>
              </tbody>
              <tfoot class="border-t-2 border-slate-200 bg-slate-50/60">
                <tr>
                  <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Total</td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ formatCurrency(order.total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- GRNs linked to this PO -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Goods Received Notes</h3>
            <Link v-if="['sent','partial'].includes(order.status)"
                  :href="route('pharmacy.grn.create') + '?purchase_order_id=' + order.id"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-600 hover:text-teal-800 transition">
              <PlusIcon class="w-3.5 h-3.5" />
              Create GRN
            </Link>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">GRN No.</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Amount</th>
                  <th class="px-4 py-2.5 w-10"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <template v-if="order.grns?.length">
                  <tr v-for="grn in order.grns" :key="grn.id"
                      class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-4 py-3 font-mono font-semibold text-slate-800">{{ grn.grn_number }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ formatDate(grn.received_date) }}</td>
                    <td class="px-4 py-3"><StatusBadge :status="grn.status" type="grn" /></td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(grn.total_amount) }}</td>
                    <td class="px-4 py-3">
                      <Link :href="route('pharmacy.grn.show', grn.id)"
                            class="p-1.5 rounded text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition">
                        <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                      </Link>
                    </td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                    No GRNs yet — goods not yet received
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
import { Link, router } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, PencilIcon, PlusIcon,
  PaperAirplaneIcon, InboxArrowDownIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout   from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Pharmacy/StatusBadge.vue'
import FormBadge   from '@/Components/Pharmacy/FormBadge.vue'

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

const props = defineProps({ order: Object })

function sendPo()   { router.patch(route('pharmacy.purchase-orders.send',   props.order.id)) }
function cancelPo() { router.patch(route('pharmacy.purchase-orders.cancel', props.order.id)) }

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>