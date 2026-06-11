<template>
  <AuthenticatedLayout

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.sales.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-slate-900 font-mono tracking-tight">
              {{ sale.invoice_number }}
            </h1>
            <span :class="statusBadgeClass" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold">
              {{ statusLabel }}
            </span>
          </div>
          <p class="text-sm text-slate-500 mt-0.5">
            {{ sale.created_at }} · Cashier: {{ sale.cashier }}
            <span v-if="sale.prescription_number"> · Rx: {{ sale.prescription_number }}</span>
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <!-- Print -->
        <button @click="printInvoice"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm transition">
          <PrinterIcon class="w-4 h-4" />
          Print
        </button>

        <!-- Return -->
        <Link v-if="['completed','partial_return'].includes(sale.status)"
              :href="route('pharmacy.sales.return.create', sale.id)"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 shadow-sm transition">
          <ArrowUturnLeftIcon class="w-4 h-4" />
          Return Items
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: Sale Info -->
      <div class="space-y-5">

        <!-- Payment -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Payment</h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-slate-500">Mode</dt>
              <dd class="font-semibold text-slate-800 capitalize">{{ sale.payment_mode?.replace('_', ' ') }}</dd>
            </div>
            <div v-if="sale.payment_reference" class="flex justify-between">
              <dt class="text-slate-500">Reference</dt>
              <dd class="font-mono text-xs text-slate-700">{{ sale.payment_reference }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Type</dt>
              <dd class="text-slate-700 capitalize">{{ sale.sale_type?.replace('_', ' ') }}</dd>
            </div>
            <div v-if="sale.pharmacist" class="flex justify-between">
              <dt class="text-slate-500">Pharmacist</dt>
              <dd class="text-slate-700">{{ sale.pharmacist }}</dd>
            </div>
          </dl>
        </div>

        <!-- Totals -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Totals</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between text-slate-600">
              <dt>Subtotal</dt>
              <dd class="font-mono">{{ formatCurrency(sale.subtotal) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>
                Discount
                <span v-if="sale.discount_value > 0" class="text-xs text-slate-400 ml-1">
                  ({{ sale.discount_type === 'percent' ? sale.discount_value + '%' : 'Rs ' + sale.discount_value }})
                </span>
              </dt>
              <dd class="font-mono text-red-600">- {{ formatCurrency(sale.discount_amount) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Tax</dt>
              <dd class="font-mono">{{ formatCurrency(sale.tax_amount) }}</dd>
            </div>
            <div class="flex justify-between font-bold text-slate-900 text-lg border-t border-slate-200 pt-2 mt-1">
              <dt>Total</dt>
              <dd class="font-mono">{{ formatCurrency(sale.total_amount) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600 pt-1">
              <dt>Paid</dt>
              <dd class="font-mono">{{ formatCurrency(sale.paid_amount) }}</dd>
            </div>
            <div v-if="sale.change_amount > 0" class="flex justify-between text-emerald-600">
              <dt>Change</dt>
              <dd class="font-mono font-semibold">{{ formatCurrency(sale.change_amount) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Returns history -->
        <div v-if="sale.returns?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Returns</h3>
          <div class="space-y-2">
            <div v-for="r in sale.returns" :key="r.id"
                 class="flex items-center justify-between text-sm">
              <div>
                <p class="font-mono font-semibold text-slate-800">{{ r.return_number }}</p>
                <p class="text-xs text-slate-400">{{ formatDate(r.return_date) }} · {{ r.refund_mode }}</p>
              </div>
              <span class="font-mono font-semibold text-red-600">
                - {{ formatCurrency(r.total_return_amount) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="sale.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Notes</h3>
          <p class="text-sm text-amber-800 leading-relaxed">{{ sale.notes }}</p>
        </div>

      </div>

      <!-- Right: Items table -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Items Dispensed</h3>
            <span class="text-xs text-slate-400">{{ sale.items?.length }} items</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
                  <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch / Exp</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Qty</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Price</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Disc%</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Subtotal</th>
                  <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Returned</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in sale.items" :key="item.id"
                    class="hover:bg-slate-50/60 transition-colors"
                    :class="item.returned_quantity > 0 ? 'bg-red-50/30' : ''">
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                    <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                      <FormBadge :form="item.form" size="xs" />
                      <span>{{ item.strength }}</span>
                      <span v-if="item.unit">· {{ item.unit }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-mono text-xs text-slate-700">{{ item.batch_number }}</div>
                    <div class="text-xs text-slate-400">Exp: {{ item.expiry_date }}</div>
                  </td>
                  <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ item.quantity }}</td>
                  <td class="px-4 py-3 text-right font-mono text-slate-600 text-xs">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-right font-mono text-slate-500 text-xs">{{ item.discount_percent }}%</td>
                  <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">{{ formatCurrency(item.subtotal) }}</td>
                  <td class="px-4 py-3 text-right">
                    <span v-if="item.returned_quantity > 0" class="font-mono text-red-600 font-semibold">
                      {{ item.returned_quantity }}
                    </span>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                </tr>
              </tbody>
              <tfoot class="border-t-2 border-slate-200 bg-slate-50/60">
                <tr>
                  <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Total</td>
                  <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 text-base">{{ formatCurrency(sale.total_amount) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Printable Invoice (hidden, shown on print) -->
    <div id="invoice-print" class="hidden print:block p-8 text-sm font-sans">
      <div class="text-center mb-6">
        <h2 class="text-xl font-bold">Pharmacy Invoice</h2>
        <p class="text-slate-600">{{ print_data.invoice_number }} · {{ print_data.invoice_date }}</p>
        <p class="text-slate-500">{{ print_data.invoice_time }}</p>
      </div>
      <table class="w-full border-collapse mb-4">
        <thead>
          <tr class="border-b-2 border-slate-800">
            <th class="text-left py-1">Medicine</th>
            <th class="text-left py-1">Batch</th>
            <th class="text-right py-1">Qty</th>
            <th class="text-right py-1">Price</th>
            <th class="text-right py-1">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in print_data.items" :key="item.medicine" class="border-b border-slate-200">
            <td class="py-1">{{ item.medicine }} {{ item.strength }}</td>
            <td class="py-1 text-xs">{{ item.batch }} / {{ item.expiry }}</td>
            <td class="py-1 text-right">{{ item.quantity }}</td>
            <td class="py-1 text-right">{{ item.unit_price }}</td>
            <td class="py-1 text-right font-semibold">{{ item.subtotal }}</td>
          </tr>
        </tbody>
      </table>
      <div class="text-right space-y-1">
        <p>Subtotal: Rs {{ print_data.subtotal }}</p>
        <p v-if="print_data.discount_amount > 0">Discount: - Rs {{ print_data.discount_amount }}</p>
        <p v-if="print_data.tax_amount > 0">Tax: Rs {{ print_data.tax_amount }}</p>
        <p class="text-lg font-bold border-t-2 border-slate-800 pt-1">Total: Rs {{ print_data.total_amount }}</p>
        <p>Paid: Rs {{ print_data.paid_amount }} ({{ print_data.payment_mode }})</p>
        <p v-if="print_data.change_amount > 0">Change: Rs {{ print_data.change_amount }}</p>
      </div>
      <p class="mt-6 text-center text-xs text-slate-500">Thank you for your visit. Get well soon!</p>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link }     from '@inertiajs/vue3'
import {
  ArrowLeftIcon, PrinterIcon, ArrowUturnLeftIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormBadge  from '@/Components/Pharmacy/FormBadge.vue'

const props = defineProps({ sale: Object, print_data: Object })

const statusBadgeClass = computed(() => ({
  completed:      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  returned:       'bg-red-50 text-red-700 ring-1 ring-red-200',
  partial_return: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
  draft:          'bg-slate-100 text-slate-500',
}[props.sale.status] ?? 'bg-slate-100 text-slate-500'))

const statusLabel = computed(() => ({
  completed:      'Completed',
  returned:       'Returned',
  partial_return: 'Partial Return',
  draft:          'Draft',
}[props.sale.status] ?? props.sale.status))

function printInvoice() { window.print() }

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<style>
@media print {
  nav, aside, header, .no-print, button { display: none !important; }
  #invoice-print { display: block !important; }
}
</style>