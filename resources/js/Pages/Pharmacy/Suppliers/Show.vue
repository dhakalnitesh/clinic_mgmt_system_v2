<template>
  <AuthenticatedLayout

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.suppliers.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ supplier.name }}</h1>
            <span :class="supplier.is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-500'"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold">
              {{ supplier.is_active ? 'Active' : 'Inactive' }}
            </span>
            <span v-if="supplier.is_license_expired"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200">
              <ExclamationTriangleIcon class="w-3 h-3" />
              License Expired
            </span>
          </div>
          <p class="text-sm text-slate-500 mt-0.5">{{ supplier.city }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <Link :href="route('pharmacy.purchase-orders.create') + '?supplier_id=' + supplier.id"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 transition">
          <PlusIcon class="w-4 h-4" />
          New Purchase Order
        </Link>
        <Link :href="route('pharmacy.suppliers.edit', supplier.id)"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm transition">
          <PencilIcon class="w-4 h-4" />
          Edit
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: Supplier Details -->
      <div class="space-y-5">

        <!-- Contact Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Contact Details</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Contact Person" :value="supplier.contact_person" />
            <InfoRow label="Phone"          :value="supplier.phone" />
            <InfoRow label="Alt. Phone"     :value="supplier.alternate_phone" />
            <InfoRow label="Email"          :value="supplier.email" />
            <InfoRow label="Address"        :value="supplier.address" />
            <InfoRow label="City"           :value="supplier.city" />
            <InfoRow label="Country"        :value="supplier.country" />
          </dl>
        </div>

        <!-- Regulatory Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Regulatory</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Drug License"  :value="supplier.drug_license_no" mono />
            <div class="flex items-start justify-between gap-4">
              <dt class="text-slate-400 shrink-0">License Expiry</dt>
              <dd class="text-right">
                <span v-if="supplier.drug_license_expiry"
                      :class="supplier.is_license_expired ? 'text-red-600 font-semibold' : 'text-slate-700'">
                  {{ formatDate(supplier.drug_license_expiry) }}
                  <span v-if="supplier.is_license_expired" class="block text-xs">⚠ Expired</span>
                </span>
                <span v-else class="text-slate-300">—</span>
              </dd>
            </div>
            <InfoRow label="PAN / VAT" :value="supplier.pan_vat_no" mono />
          </dl>
        </div>

        <!-- Financial Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Financial Terms</h3>
          <dl class="space-y-3 text-sm">
            <InfoRow label="Credit Days"    :value="`${supplier.credit_days} days`" />
            <InfoRow label="Credit Limit"   :value="formatCurrency(supplier.credit_limit)" mono />
            <InfoRow label="Opening Balance":value="formatCurrency(supplier.opening_balance)" mono />
            <div class="border-t border-slate-100 pt-3">
              <InfoRow label="Total Purchases" :value="formatCurrency(supplier.total_purchases)" mono />
            </div>
          </dl>
        </div>

        <!-- Notes -->
        <div v-if="supplier.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Notes</h3>
          <p class="text-sm text-amber-800 leading-relaxed">{{ supplier.notes }}</p>
        </div>

      </div>

      <!-- Right: Purchase Order History -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Purchase Order History</h3>
            <span class="text-xs text-slate-400">{{ supplier.purchase_orders_count }} total orders</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">PO Number</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Date</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Amount</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Ordered By</th>
                  <th class="px-4 py-3 w-10"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <template v-if="supplier.recent_orders?.length">
                  <tr v-for="po in supplier.recent_orders" :key="po.id"
                      class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-4 py-3 font-mono font-semibold text-slate-800">{{ po.po_number }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ formatDate(po.order_date) }}</td>
                    <td class="px-4 py-3"><StatusBadge :status="po.status" type="po" /></td>
                    <td class="px-4 py-3 text-right font-mono font-semibold text-slate-800">{{ formatCurrency(po.total_amount) }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ po.ordered_by }}</td>
                    <td class="px-4 py-3">
                      <Link :href="route('pharmacy.purchase-orders.show', po.id)"
                            class="p-1.5 rounded text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition">
                        <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                      </Link>
                    </td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                    No purchase orders yet
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
import { Link } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, PlusIcon, PencilIcon,
  ArrowTopRightOnSquareIcon, ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Pharmacy/StatusBadge.vue'

const InfoRow = {
  props: ['label', 'value', 'mono'],
  template: `
    <div class="flex items-start justify-between gap-4">
      <dt class="text-slate-400 shrink-0">{{ label }}</dt>
      <dd class="text-right text-slate-700 font-medium break-all" :class="mono ? 'font-mono text-xs' : ''">
        {{ value ?? '—' }}
      </dd>
    </div>
  `,
}

defineProps({ supplier: Object })

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>