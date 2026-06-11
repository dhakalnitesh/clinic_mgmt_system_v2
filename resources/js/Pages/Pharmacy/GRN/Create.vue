<template>
  <AuthenticatedLayout>

    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.purchase-orders.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Receive Goods</h1>
        <p class="text-sm text-slate-500 mt-0.5">
          <template v-if="purchase_order">
            Against PO: <span class="font-semibold text-teal-700">{{ purchase_order.po_number }}</span>
            · {{ purchase_order.supplier?.name }}
          </template>
          <template v-else>Standalone GRN — no purchase order</template>
        </p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- GRN Header -->
      <FormSection title="Receipt Details" description="Supplier, date and supplier invoice reference">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

          <FormField label="Supplier" required :error="form.errors.supplier_id">
            <select v-model="form.supplier_id" class="form-select"
                    :disabled="!!purchase_order">
              <option value="">Select supplier…</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </FormField>

          <FormField label="Received Date" required :error="form.errors.received_date">
            <input v-model="form.received_date" type="date" class="form-input" />
          </FormField>

          <FormField label="Supplier Invoice No." :error="form.errors.invoice_number">
            <input v-model="form.invoice_number" type="text" class="form-input font-mono"
                   placeholder="e.g. INV-2024-001" />
          </FormField>

          <FormField label="Invoice Date" :error="form.errors.invoice_date">
            <input v-model="form.invoice_date" type="date" class="form-input" />
          </FormField>

          <FormField label="Notes" :error="form.errors.notes" class="md:col-span-2">
            <textarea v-model="form.notes" rows="1" class="form-input resize-none"
                      placeholder="Any notes about this delivery…" />
          </FormField>

        </div>
      </FormSection>

      <!-- Items from PO (pre-populated) -->
      <FormSection title="Items Received"
                   description="Enter batch details for each item received. All fields per batch are required.">

        <!-- Add medicine (only for standalone GRN) -->
        <div v-if="!purchase_order" class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Add Medicine</label>
          <DrugSearchInput
            placeholder="Search medicine to add…"
            :search-url="route('pharmacy.medicines.search')"
            @select="addItem"
          />
        </div>

        <p v-if="form.errors.items" class="mb-3 text-xs text-red-600 flex items-center gap-1">
          <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0" />
          {{ form.errors.items }}
        </p>

        <!-- Items -->
        <div class="space-y-4">
          <div v-for="(item, idx) in form.items" :key="item._key"
               class="border border-slate-200 rounded-xl overflow-hidden">

            <!-- Item Header -->
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                  {{ idx + 1 }}
                </span>
                <div>
                  <span class="font-semibold text-slate-800">{{ item.medicine_name }}</span>
                  <span v-if="item.strength" class="ml-1.5 text-xs text-slate-500">{{ item.strength }}</span>
                  <FormBadge v-if="item.form" :form="item.form" size="xs" class="ml-1.5" />
                </div>
              </div>
              <div class="flex items-center gap-3">
                <!-- PO reference info -->
                <div v-if="item.po_item_id" class="text-xs text-slate-500 text-right">
                  <span class="text-slate-400">PO qty:</span>
                  <span class="font-mono font-semibold ml-1">{{ item.po_quantity_ordered }}</span>
                  <span class="text-slate-300 mx-1">|</span>
                  <span class="text-slate-400">Pending:</span>
                  <span class="font-mono font-semibold ml-1"
                        :class="item.po_pending > 0 ? 'text-amber-600' : 'text-slate-400'">
                    {{ item.po_pending }}
                  </span>
                </div>
                <button type="button" @click="removeItem(idx)"
                        class="p-1.5 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 transition">
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Batch Fields Grid -->
            <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-4">

              <FormField label="Batch Number" required
                         :error="form.errors[`items.${idx}.batch_number`]">
                <input v-model="item.batch_number" type="text"
                       class="form-input font-mono" placeholder="e.g. BN2024001" />
              </FormField>

              <FormField label="Manufacturing Date"
                         :error="form.errors[`items.${idx}.manufacturing_date`]">
                <input v-model="item.manufacturing_date" type="date" class="form-input" />
              </FormField>

              <FormField label="Expiry Date" required
                         :error="form.errors[`items.${idx}.expiry_date`]">
                <input v-model="item.expiry_date" type="date" class="form-input"
                       :class="expiryClass(item.expiry_date)" />
              </FormField>

              <FormField label="Qty Received" required
                         :error="form.errors[`items.${idx}.quantity_received`]">
                <input v-model.number="item.quantity_received" type="number" min="1"
                       @input="recalcItem(idx)"
                       class="form-input font-mono text-right" />
              </FormField>

              <FormField label="Free Qty" :error="form.errors[`items.${idx}.free_quantity`]">
                <input v-model.number="item.free_quantity" type="number" min="0"
                       class="form-input font-mono text-right" />
                <template #hint>Bonus stock from supplier</template>
              </FormField>

              <FormField label="Purchase Price (Rs)" required
                         :error="form.errors[`items.${idx}.unit_price`]">
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Rs</span>
                  <input v-model.number="item.unit_price" type="number" min="0" step="0.01"
                         @input="recalcItem(idx)"
                         class="form-input pl-8 font-mono text-right" />
                </div>
              </FormField>

              <FormField label="Sale Price (Rs)" required
                         :error="form.errors[`items.${idx}.sale_price`]">
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Rs</span>
                  <input v-model.number="item.sale_price" type="number" min="0" step="0.01"
                         class="form-input pl-8 font-mono text-right" />
                </div>
              </FormField>

              <FormField label="MRP (Rs)" :error="form.errors[`items.${idx}.mrp`]">
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Rs</span>
                  <input v-model.number="item.mrp" type="number" min="0" step="0.01"
                         class="form-input pl-8 font-mono text-right" />
                </div>
              </FormField>

              <FormField label="Discount %" :error="form.errors[`items.${idx}.discount_percent`]">
                <input v-model.number="item.discount_percent" type="number" min="0" max="100" step="0.01"
                       @input="recalcItem(idx)"
                       class="form-input font-mono text-right" />
              </FormField>

              <FormField label="Tax %" :error="form.errors[`items.${idx}.tax_percent`]">
                <input v-model.number="item.tax_percent" type="number" min="0" max="100" step="0.01"
                       @input="recalcItem(idx)"
                       class="form-input font-mono text-right" />
              </FormField>

              <!-- Subtotal -->
              <div class="md:col-span-2 flex items-end">
                <div class="w-full rounded-lg bg-teal-50 border border-teal-200 px-4 py-2.5 flex justify-between items-center">
                  <span class="text-xs font-semibold text-teal-700 uppercase tracking-wide">Item Total</span>
                  <span class="font-mono font-bold text-teal-800">{{ formatCurrency(item.subtotal) }}</span>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- Empty -->
        <div v-if="!form.items.length" class="rounded-xl border-2 border-dashed border-slate-200 py-12 text-center text-slate-400">
          <InboxArrowDownIcon class="w-8 h-8 mx-auto mb-2 opacity-40" />
          <p class="text-sm">No items yet</p>
        </div>

        <!-- GRN Totals -->
        <div v-if="form.items.length" class="mt-5 flex justify-end">
          <dl class="w-72 space-y-1.5 text-sm">
            <div class="flex justify-between text-slate-600">
              <dt>Items</dt>
              <dd class="font-mono">{{ form.items.length }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Total Qty</dt>
              <dd class="font-mono">{{ totalQty }}</dd>
            </div>
            <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-2">
              <dt>Grand Total</dt>
              <dd class="font-mono text-lg">{{ formatCurrency(grandTotal) }}</dd>
            </div>
          </dl>
        </div>

      </FormSection>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-2 pb-6">
        <Link :href="route('pharmacy.purchase-orders.index')"
              class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing || !form.items.length"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 disabled:opacity-60 active:scale-95 transition-all">
          <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
          <InboxArrowDownIcon v-else class="w-4 h-4" />
          Create GRN
        </button>
      </div>

    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, ArrowPathIcon, XMarkIcon,
  InboxArrowDownIcon, ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout       from '@/Layouts/AuthenticatedLayout.vue'
import FormSection     from '@/Components/FormSection.vue'
import FormField       from '@/Components/FormField.vue'
import FormBadge       from '@/Components/Pharmacy/FormBadge.vue'
import DrugSearchInput from '@/Components/Pharmacy/DrugSearchInput.vue'

const props = defineProps({
  suppliers:      { type: Array,  default: () => [] },
  today:          { type: String, default: '' },
  purchase_order: { type: Object, default: null },
})

let keyCounter = 0

// Pre-populate items from PO pending items
const initialItems = (props.purchase_order?.items ?? []).map(i => buildItem({
  medicine_id:        i.medicine_id,
  medicine_name:      i.medicine_name,
  strength:           i.strength,
  form:               i.form,
  unit:               i.unit,
  po_item_id:         i.id,
  po_quantity_ordered: i.quantity_ordered,
  po_pending:         i.pending_quantity,
  unit_price:         i.unit_price,
  sale_price:         0,
  quantity_received:  i.pending_quantity,
}))

const form = useForm({
  supplier_id:       props.purchase_order?.supplier?.id ?? '',
  purchase_order_id: props.purchase_order?.id ?? null,
  received_date:     props.today,
  invoice_number:    '',
  invoice_date:      '',
  notes:             '',
  items:             initialItems,
})

// ── Computed ───────────────────────────────────────────────────────
const grandTotal = computed(() =>
  form.items.reduce((s, i) => s + (i.subtotal || 0), 0)
)
const totalQty = computed(() =>
  form.items.reduce((s, i) => s + (i.quantity_received || 0) + (i.free_quantity || 0), 0)
)

// ── Helpers ────────────────────────────────────────────────────────
function buildItem(data) {
  return {
    _key:               keyCounter++,
    medicine_id:        data.medicine_id,
    medicine_name:      data.medicine_name,
    strength:           data.strength ?? '',
    form:               data.form ?? '',
    unit:               data.unit ?? '',
    purchase_order_item_id: data.po_item_id ?? null,
    po_quantity_ordered: data.po_quantity_ordered ?? null,
    po_pending:         data.po_pending ?? null,
    batch_number:       '',
    manufacturing_date: '',
    expiry_date:        '',
    quantity_received:  data.quantity_received ?? 1,
    free_quantity:      0,
    unit_price:         data.unit_price ?? 0,
    sale_price:         data.sale_price ?? 0,
    mrp:                0,
    discount_percent:   0,
    tax_percent:        0,
    subtotal:           0,
  }
}

function addItem(med) {
  const exists = form.items.find(i => i.medicine_id === med.id)
  if (exists) return
  form.items.push(buildItem({
    medicine_id:   med.id,
    medicine_name: med.name,
    strength:      med.strength,
    form:          med.form,
    unit:          med.unit,
    unit_price:    parseFloat(med.purchase_price ?? 0),
    sale_price:    parseFloat(med.sale_price ?? 0),
  }))
}

function removeItem(idx) { form.items.splice(idx, 1) }

function recalcItem(idx) {
  const i    = form.items[idx]
  const gross = i.quantity_received * i.unit_price
  const after = gross - (gross * (i.discount_percent || 0) / 100)
  i.subtotal  = Math.round((after + (after * (i.tax_percent || 0) / 100)) * 100) / 100
}

function expiryClass(date) {
  if (!date) return ''
  const days = Math.ceil((new Date(date) - new Date()) / 86400000)
  if (days < 0)   return 'border-red-400 focus:border-red-500'
  if (days < 90)  return 'border-amber-400 focus:border-amber-500'
  return ''
}

function submit() {
  const payload = {
    supplier_id:       form.supplier_id,
    purchase_order_id: form.purchase_order_id,
    received_date:     form.received_date,
    invoice_number:    form.invoice_number,
    invoice_date:      form.invoice_date,
    notes:             form.notes,
    items: form.items.map(i => ({
      medicine_id:             i.medicine_id,
      purchase_order_item_id:  i.purchase_order_item_id,
      batch_number:            i.batch_number,
      manufacturing_date:      i.manufacturing_date || null,
      expiry_date:             i.expiry_date,
      quantity_received:       i.quantity_received,
      free_quantity:           i.free_quantity || 0,
      unit_price:              i.unit_price,
      sale_price:              i.sale_price,
      mrp:                     i.mrp || null,
      discount_percent:        i.discount_percent || 0,
      tax_percent:             i.tax_percent || 0,
    })),
  }

  form.transform(() => payload).post(route('pharmacy.grn.store'))
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
</script>