<template>
  <AuthenticatedLayout :title="medicine.name">

    <!-- ── Header ─────────────────────────────────────────────────── -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.medicines.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ medicine.name }}</h1>
            <FormBadge :form="medicine.form" />
            <StockBadge :status="medicine.stock_status" :count="medicine.total_stock" :show-count="true" />
            <span v-if="medicine.is_prescription_required" class="badge-rx">Rx</span>
            <span v-if="medicine.is_controlled" class="badge-cd">CD</span>
            <span v-if="!medicine.is_active" class="badge-inactive">Inactive</span>
          </div>
          <p class="text-sm text-slate-500 mt-0.5">
            {{ medicine.generic?.name }}
            <span v-if="medicine.strength" class="text-slate-400"> · {{ medicine.strength }}</span>
            <span class="text-slate-400"> · {{ medicine.category?.name }}</span>
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <Link :href="route('pharmacy.medicines.edit', medicine.id)"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm transition">
          <PencilIcon class="w-4 h-4" />
          Edit
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- ── Left Column ─────────────────────────────────────────── -->
      <div class="space-y-5">

        <!-- Pricing Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Pricing</h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-slate-500">Purchase Price</dt>
              <dd class="font-mono font-semibold text-slate-700">{{ formatCurrency(medicine.purchase_price) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Sale Price</dt>
              <dd class="font-mono font-semibold text-slate-900">{{ formatCurrency(medicine.sale_price) }}</dd>
            </div>
            <div v-if="medicine.mrp" class="flex justify-between">
              <dt class="text-slate-500">MRP</dt>
              <dd class="font-mono text-slate-600">{{ formatCurrency(medicine.mrp) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Tax</dt>
              <dd class="font-mono text-slate-600">{{ medicine.tax_percent }}%</dd>
            </div>
            <div class="border-t border-slate-100 pt-3 flex justify-between">
              <dt class="text-slate-500">Markup</dt>
              <dd :class="['font-mono font-semibold', medicine.markup_percent >= 0 ? 'text-emerald-600' : 'text-red-600']">
                {{ medicine.markup_percent }}%
              </dd>
            </div>
          </dl>
        </div>

        <!-- Stock Control Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Stock Control</h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-slate-500">Total Stock</dt>
              <dd class="font-mono font-bold text-slate-900">{{ medicine.total_stock.toLocaleString() }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Reorder Level</dt>
              <dd class="font-mono text-slate-600">{{ medicine.reorder_level }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Reorder Qty</dt>
              <dd class="font-mono text-slate-600">{{ medicine.reorder_quantity }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Shelf</dt>
              <dd class="font-mono text-slate-600">{{ medicine.shelf_location ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Nearest Expiry</dt>
              <dd class="font-mono text-slate-600">{{ medicine.nearest_expiry ? formatDate(medicine.nearest_expiry) : '—' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Details Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">Details</h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-slate-500">Generic</dt>
              <dd class="text-slate-700 text-right">{{ medicine.generic?.name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Category</dt>
              <dd class="text-slate-700">{{ medicine.category?.name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Unit</dt>
              <dd class="text-slate-700">{{ medicine.unit?.name }} ({{ medicine.unit?.abbreviation }})</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Manufacturer</dt>
              <dd class="text-slate-700">{{ medicine.manufacturer ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">Barcode</dt>
              <dd class="font-mono text-slate-600 text-xs">{{ medicine.barcode ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-500">HSN Code</dt>
              <dd class="font-mono text-slate-600 text-xs">{{ medicine.hsn_code ?? '—' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Notes -->
        <div v-if="medicine.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <h3 class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">Notes</h3>
          <p class="text-sm text-amber-800 leading-relaxed">{{ medicine.notes }}</p>
        </div>

      </div>

      <!-- ── Right Column: Batches ──────────────────────────────────── -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Stock Batches</h3>
            <span class="text-xs text-slate-400">{{ medicine.batches?.length ?? 0 }} active batches</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch No.</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Expiry</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Available</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Sold</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Cost</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Sell</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Supplier</th>
                  <th class="px-4 py-3 w-10"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <template v-if="medicine.batches?.length">
                  <tr v-for="batch in medicine.batches" :key="batch.id"
                      class="hover:bg-slate-50/60 transition-colors"
                      :class="{
                        'bg-red-50/40':    batch.expiry_status === 'expired',
                        'bg-orange-50/30': batch.expiry_status === 'critical',
                        'bg-amber-50/20':  batch.expiry_status === 'warning',
                      }">

                    <td class="px-4 py-3">
                      <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded">
                        {{ batch.batch_number }}
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <ExpiryTag :date="batch.expiry_date" :status="batch.expiry_status" :days="batch.days_to_expiry" />
                    </td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                      {{ batch.quantity_available.toLocaleString() }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-slate-500">
                      {{ batch.quantity_sold.toLocaleString() }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-xs text-slate-500">
                      {{ formatCurrency(batch.purchase_price) }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-slate-700">
                      {{ formatCurrency(batch.sale_price) }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ batch.supplier ?? '—' }}</td>
                    <td class="px-4 py-3">
                      <Link :href="route('pharmacy.inventory.batch', batch.id)"
                            class="p-1.5 rounded text-slate-400 hover:text-teal-600 hover:bg-teal-50 transition">
                        <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                      </Link>
                    </td>
                  </tr>
                </template>

                <tr v-else>
                  <td colspan="8" class="px-4 py-12 text-center text-slate-400 text-sm">
                    No active stock batches — receive stock via a Purchase Order / GRN
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totals Footer -->
          <div v-if="medicine.batches?.length" class="border-t border-slate-200 bg-slate-50/60 px-5 py-3 flex justify-end gap-8 text-sm">
            <span class="text-slate-500">
              Total Available:
              <span class="font-mono font-bold text-slate-900 ml-1">{{ medicine.total_stock.toLocaleString() }}</span>
            </span>
            <span class="text-slate-500">
              Stock Value:
              <span class="font-mono font-bold text-teal-700 ml-1">{{ formatCurrency(stockValue) }}</span>
            </span>
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, PencilIcon, ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline'
import FormBadge  from '@/Components/Pharmacy/FormBadge.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StockBadge from '@/Components/Pharmacy/StockBadge.vue'
import ExpiryTag  from '@/Components/Pharmacy/ExpiryTag.vue'

const props = defineProps({ medicine: Object })

const stockValue = computed(() =>
  (props.medicine.batches ?? []).reduce(
    (sum, b) => sum + b.quantity_available * b.purchase_price, 0
  )
)

function formatCurrency(val) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(val ?? 0)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>