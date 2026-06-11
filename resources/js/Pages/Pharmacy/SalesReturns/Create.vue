<template>
  <AuthenticatedLayout

    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.sales.show', sale.id)"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Process Return</h1>
        <p class="text-sm text-slate-500 mt-0.5">
          Invoice: <span class="font-mono font-semibold text-slate-700">{{ sale.invoice_number }}</span>
          · {{ formatDate(sale.sale_date) }}
          · <span class="font-semibold">{{ formatCurrency(sale.total_amount) }}</span>
        </p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- Return Header -->
      <FormSection title="Return Details" description="Reason and refund method">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

          <FormField label="Return Date" required :error="form.errors.return_date">
            <input v-model="form.return_date" type="date" class="form-input" />
          </FormField>

          <FormField label="Reason" required :error="form.errors.reason">
            <select v-model="form.reason" class="form-select">
              <option value="">Select reason…</option>
              <option value="wrong_medicine">Wrong Medicine</option>
              <option value="wrong_quantity">Wrong Quantity</option>
              <option value="adverse_reaction">Adverse Reaction</option>
              <option value="prescription_changed">Prescription Changed</option>
              <option value="patient_refused">Patient Refused</option>
              <option value="duplicate_sale">Duplicate Sale</option>
              <option value="other">Other</option>
            </select>
          </FormField>

          <FormField label="Refund Method" required :error="form.errors.refund_mode">
            <select v-model="form.refund_mode" class="form-select">
              <option value="cash">Cash</option>
              <option value="card">Card Reversal</option>
              <option value="upi">UPI</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="credit_note">Credit Note</option>
            </select>
          </FormField>

          <FormField label="Notes" :error="form.errors.notes" class="md:col-span-3">
            <textarea v-model="form.notes" rows="2" class="form-input resize-none"
                      placeholder="Optional notes about this return…" />
          </FormField>

        </div>
      </FormSection>

      <!-- Items to Return -->
      <FormSection title="Items to Return"
                   description="Select items and quantities to return. Choose whether to restock or write off.">

        <p v-if="form.errors.items" class="mb-3 text-xs text-red-600 flex items-center gap-1">
          <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0" />
          {{ form.errors.items }}
        </p>

        <div class="border border-slate-200 rounded-xl overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide w-8">
                  <input type="checkbox" @change="toggleAll" :checked="allSelected"
                         class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                </th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
                <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide w-24">Max Qty</th>
                <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide w-28">Return Qty</th>
                <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide w-36">Stock Action</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Condition Note</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide w-28">Refund</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(item, idx) in form.items" :key="item.sale_item_id"
                  :class="[
                    'transition-colors',
                    item.selected ? 'bg-teal-50/30 hover:bg-teal-50/50' : 'hover:bg-slate-50/40 opacity-60',
                  ]">

                <!-- Checkbox -->
                <td class="px-4 py-3">
                  <input type="checkbox" v-model="item.selected"
                         @change="onToggleItem(idx)"
                         class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                </td>

                <!-- Medicine -->
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                  <div class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                    <FormBadge :form="item.form" size="xs" />
                    <span>{{ item.strength }}</span>
                    <span class="font-mono">· {{ item.batch_number }}</span>
                    <span class="text-slate-300">·</span>
                    <span>Exp {{ item.expiry_date }}</span>
                  </div>
                </td>

                <!-- Max returnable qty -->
                <td class="px-4 py-3 text-center font-mono font-semibold text-slate-700">
                  {{ item.returnable_quantity }}
                </td>

                <!-- Return quantity input -->
                <td class="px-4 py-3 text-center">
                  <input v-model.number="item.quantity_returned"
                         type="number"
                         :min="1"
                         :max="item.returnable_quantity"
                         :disabled="!item.selected"
                         @input="recalcItem(idx)"
                         class="w-20 text-center font-mono form-input py-1.5 disabled:opacity-40" />
                </td>

                <!-- Stock action -->
                <td class="px-4 py-3">
                  <div class="flex flex-col gap-1">
                    <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                      <input type="radio" v-model="item.stock_action" value="return_to_stock"
                             :disabled="!item.selected"
                             class="text-teal-600 focus:ring-teal-500" />
                      <span class="text-emerald-700 font-medium">Restock</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                      <input type="radio" v-model="item.stock_action" value="write_off"
                             :disabled="!item.selected"
                             class="text-red-500 focus:ring-red-400" />
                      <span class="text-red-600 font-medium">Write off</span>
                    </label>
                  </div>
                </td>

                <!-- Condition note -->
                <td class="px-4 py-3">
                  <input v-model="item.condition_note"
                         type="text"
                         :disabled="!item.selected"
                         placeholder="e.g. Damaged packaging"
                         class="form-input py-1.5 text-xs disabled:opacity-40" />
                </td>

                <!-- Refund amount -->
                <td class="px-4 py-3 text-right font-mono font-semibold"
                    :class="item.selected ? 'text-teal-700' : 'text-slate-300'">
                  {{ item.selected ? formatCurrency(item.refund_amount) : '—' }}
                </td>

              </tr>
            </tbody>
            <tfoot class="border-t-2 border-slate-200 bg-slate-50">
              <tr>
                <td colspan="6" class="px-4 py-3 text-right text-sm font-semibold text-slate-700">
                  Total Refund Amount
                </td>
                <td class="px-4 py-3 text-right font-mono font-bold text-lg text-teal-700">
                  {{ formatCurrency(totalRefund) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

      </FormSection>

      <!-- Actions -->
      <div class="flex items-center justify-between gap-3 pt-2 pb-6">
        <div class="text-sm text-slate-500">
          <span class="font-semibold text-slate-800">{{ selectedCount }}</span>
          item{{ selectedCount !== 1 ? 's' : '' }} selected for return
          · Refund: <span class="font-semibold text-teal-700">{{ formatCurrency(totalRefund) }}</span>
        </div>
        <div class="flex items-center gap-3">
          <Link :href="route('pharmacy.sales.show', sale.id)"
                class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
            Cancel
          </Link>
          <button type="submit"
                  :disabled="form.processing || selectedCount === 0"
                  class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 disabled:opacity-60 active:scale-95 transition-all">
            <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
            <ArrowUturnLeftIcon v-else class="w-4 h-4" />
            Process Return
          </button>
        </div>
      </div>

    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, ArrowPathIcon, ArrowUturnLeftIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormSection from '@/Components/FormSection.vue'
import FormField   from '@/Components/FormField.vue'
import FormBadge   from '@/Components/Pharmacy/FormBadge.vue'

const props = defineProps({ sale: Object })

// Build form items from returnable sale items
const form = useForm({
  sale_id:     props.sale.id,
  return_date: new Date().toISOString().split('T')[0],
  reason:      '',
  refund_mode: 'cash',
  notes:       '',
  items: props.sale.items.map(i => ({
    sale_item_id:       i.id,
    medicine_id:        i.medicine_id,
    medicine_name:      i.medicine_name,
    strength:           i.strength,
    form:               i.form,
    batch_number:       i.batch_number,
    expiry_date:        i.expiry_date,
    stock_batch_id:     i.stock_batch_id,
    returnable_quantity:i.returnable_quantity,
    unit_price:         i.unit_price,
    discount_percent:   i.discount_percent,
    tax_percent:        i.tax_percent,
    selected:           false,
    quantity_returned:  i.returnable_quantity,
    stock_action:       'return_to_stock',
    condition_note:     '',
    refund_amount:      0,
  })),
})

// ── Computed ───────────────────────────────────────────────────────
const selectedCount = computed(() => form.items.filter(i => i.selected).length)

const allSelected = computed(() =>
  form.items.length > 0 && form.items.every(i => i.selected)
)

const totalRefund = computed(() =>
  form.items.filter(i => i.selected).reduce((s, i) => s + (i.refund_amount || 0), 0)
)

// ── Methods ────────────────────────────────────────────────────────
function toggleAll(e) {
  form.items.forEach((item, idx) => {
    item.selected = e.target.checked
    if (item.selected) recalcItem(idx)
    else item.refund_amount = 0
  })
}

function onToggleItem(idx) {
  const item = form.items[idx]
  if (item.selected) recalcItem(idx)
  else item.refund_amount = 0
}

function recalcItem(idx) {
  const item  = form.items[idx]
  const qty   = Math.min(item.quantity_returned || 0, item.returnable_quantity)
  const gross = qty * item.unit_price
  const after = gross - (gross * item.discount_percent / 100)
  item.refund_amount = Math.round((after + (after * item.tax_percent / 100)) * 100) / 100
}

function submit() {
  const selected = form.items.filter(i => i.selected)
  if (selected.length === 0) return

  const payload = {
    sale_id:     form.sale_id,
    return_date: form.return_date,
    reason:      form.reason,
    refund_mode: form.refund_mode,
    notes:       form.notes,
    items: selected.map(i => ({
      sale_item_id:      i.sale_item_id,
      quantity_returned: i.quantity_returned,
      stock_action:      i.stock_action,
      condition_note:    i.condition_note,
    })),
  }

  form.transform(() => payload).post(route('pharmacy.sales-returns.store'))
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>