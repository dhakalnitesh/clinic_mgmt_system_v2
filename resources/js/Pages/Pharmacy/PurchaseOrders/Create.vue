<template>
  <AuthenticatedLayout :title="isEditing ? `Edit ${editing.po_number}` : 'New Purchase Order'">

    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.purchase-orders.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          {{ isEditing ? `Edit ${editing.po_number}` : 'New Purchase Order' }}
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">
          {{ isEditing ? 'Only draft orders can be edited' : 'Create a new purchase order for a supplier' }}
        </p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- PO Header -->
      <FormSection title="Order Details" description="Supplier and order date">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <FormField label="Supplier" required :error="form.errors.supplier_id" class="md:col-span-1">
            <select v-model="form.supplier_id" class="form-select">
              <option value="">Select supplier…</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </FormField>
          <FormField label="Order Date" required :error="form.errors.order_date">
            <input v-model="form.order_date" type="date" class="form-input" />
          </FormField>
          <FormField label="Expected Delivery" :error="form.errors.expected_delivery_date">
            <input v-model="form.expected_delivery_date" type="date" class="form-input" />
          </FormField>
          <FormField label="Notes" :error="form.errors.notes" class="md:col-span-3">
            <textarea v-model="form.notes" rows="2" class="form-input resize-none" placeholder="Any special instructions for this order…" />
          </FormField>
        </div>
      </FormSection>

      <!-- Line Items -->
      <FormSection title="Order Items" description="Search and add medicines to order">

        <!-- Medicine Search -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Search & Add Medicine
          </label>
          <DrugSearchInput
            ref="searchRef"
            placeholder="Search medicine to add to order…"
            :search-url="route('pharmacy.medicines.search')"
            @select="addItem"
            @clear="() => {}"
          />
          <p v-if="form.errors.items" class="mt-1.5 text-xs text-red-600">{{ form.errors.items }}</p>
        </div>

        <!-- Items Table -->
        <div v-if="form.items.length" class="border border-slate-200 rounded-xl overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide w-28">Qty</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide w-32">Unit Price (Rs)</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide w-24">Disc %</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide w-24">Tax %</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide w-32">Subtotal</th>
                <th class="px-4 py-2.5 w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(item, idx) in form.items" :key="item._key"
                  class="hover:bg-slate-50/40 transition-colors">

                <!-- Medicine -->
                <td class="px-4 py-2.5">
                  <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                  <div class="text-xs text-slate-400 flex items-center gap-1.5 mt-0.5">
                    <FormBadge :form="item.form" size="xs" />
                    <span>{{ item.strength }}</span>
                    <span v-if="item.unit" class="text-slate-300">·</span>
                    <span>{{ item.unit }}</span>
                  </div>
                </td>

                <!-- Quantity -->
                <td class="px-4 py-2.5">
                  <input v-model.number="item.quantity_ordered"
                         @input="recalcItem(idx)"
                         type="number" min="1"
                         class="w-full text-right font-mono form-input py-1.5 px-2" />
                </td>

                <!-- Unit Price -->
                <td class="px-4 py-2.5">
                  <input v-model.number="item.unit_price"
                         @input="recalcItem(idx)"
                         type="number" min="0" step="0.01"
                         class="w-full text-right font-mono form-input py-1.5 px-2" />
                </td>

                <!-- Discount -->
                <td class="px-4 py-2.5">
                  <input v-model.number="item.discount_percent"
                         @input="recalcItem(idx)"
                         type="number" min="0" max="100" step="0.01"
                         class="w-full text-right font-mono form-input py-1.5 px-2" />
                </td>

                <!-- Tax -->
                <td class="px-4 py-2.5">
                  <input v-model.number="item.tax_percent"
                         @input="recalcItem(idx)"
                         type="number" min="0" max="100" step="0.01"
                         class="w-full text-right font-mono form-input py-1.5 px-2" />
                </td>

                <!-- Subtotal -->
                <td class="px-4 py-2.5 text-right font-mono font-semibold text-slate-800">
                  {{ formatCurrency(item.subtotal) }}
                </td>

                <!-- Remove -->
                <td class="px-4 py-2.5 text-center">
                  <button type="button" @click="removeItem(idx)"
                          class="p-1 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 transition">
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </td>

              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty state -->
        <div v-else class="rounded-xl border-2 border-dashed border-slate-200 py-12 text-center text-slate-400">
          <BeakerIcon class="w-8 h-8 mx-auto mb-2 opacity-40" />
          <p class="text-sm">Search above to add medicines to this order</p>
        </div>

        <!-- Totals -->
        <div v-if="form.items.length" class="mt-4 flex justify-end">
          <dl class="w-72 space-y-1.5 text-sm">
            <div class="flex justify-between text-slate-600">
              <dt>Subtotal</dt>
              <dd class="font-mono">{{ formatCurrency(totals.subtotal) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Tax</dt>
              <dd class="font-mono">{{ formatCurrency(totals.tax) }}</dd>
            </div>
            <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-2 mt-2">
              <dt>Total</dt>
              <dd class="font-mono text-lg">{{ formatCurrency(totals.total) }}</dd>
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
          <CheckIcon v-else class="w-4 h-4" />
          {{ isEditing ? 'Update Order' : 'Save as Draft' }}
        </button>
      </div>

    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, ArrowPathIcon, CheckIcon,
  XMarkIcon, BeakerIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormSection    from '@/Components/FormSection.vue'
import FormField      from '@/Components/FormField.vue'
import FormBadge      from '@/Components/Pharmacy/FormBadge.vue'
import DrugSearchInput from '@/Components/Pharmacy/DrugSearchInput.vue'

const props = defineProps({
  suppliers:        { type: Array, default: () => [] },
  selected_supplier:{ type: Object, default: null },
  today:            { type: String, default: '' },
  editing:          { type: Object, default: null },
})

const isEditing = computed(() => !!props.editing)
let keyCounter  = 0

// ── Build initial items from editing data ──────────────────────────
const initialItems = (props.editing?.items ?? []).map(i => ({
  _key:             keyCounter++,
  id:               i.id,
  medicine_id:      i.medicine_id,
  medicine_name:    i.medicine_name,
  strength:         i.strength,
  form:             i.form,
  unit:             i.unit,
  quantity_ordered:  i.quantity_ordered,
  unit_price:       i.unit_price,
  discount_percent:  i.discount_percent,
  tax_percent:      i.tax_percent,
  subtotal:         i.subtotal,
}))

const form = useForm({
  supplier_id:             props.editing?.supplier_id ?? props.selected_supplier?.id ?? '',
  order_date:              props.editing?.order_date  ?? props.today,
  expected_delivery_date:  props.editing?.expected_delivery_date ?? '',
  notes:                   props.editing?.notes ?? '',
  items:                   initialItems,
})

// ── Computed totals ────────────────────────────────────────────────
const totals = computed(() => {
  const subtotal = form.items.reduce((s, i) => s + (i.subtotal || 0), 0)
  const tax      = form.items.reduce((s, i) => {
    const gross   = i.quantity_ordered * i.unit_price
    const after   = gross - (gross * i.discount_percent / 100)
    return s + (after * i.tax_percent / 100)
  }, 0)
  return { subtotal, tax, total: subtotal }
})

// ── Add item from DrugSearchInput ──────────────────────────────────
function addItem(med) {
  const exists = form.items.find(i => i.medicine_id === med.id)
  if (exists) { exists.quantity_ordered++; recalcItem(form.items.indexOf(exists)); return }

  const item = {
    _key:             keyCounter++,
    id:               null,
    medicine_id:      med.id,
    medicine_name:    med.name,
    strength:         med.strength,
    form:             med.form,
    unit:             med.unit,
    quantity_ordered:  1,
    unit_price:       parseFloat(med.purchase_price ?? 0),
    discount_percent:  0,
    tax_percent:      parseFloat(med.tax_percent ?? 0),
    subtotal:         0,
  }
  form.items.push(item)
  recalcItem(form.items.length - 1)
}

function removeItem(idx) { form.items.splice(idx, 1) }

function recalcItem(idx) {
  const i     = form.items[idx]
  const gross = i.quantity_ordered * i.unit_price
  const after = gross - (gross * i.discount_percent / 100)
  i.subtotal  = Math.round((after + (after * i.tax_percent / 100)) * 100) / 100
}

function submit() {
  const payload = {
    supplier_id:            form.supplier_id,
    order_date:             form.order_date,
    expected_delivery_date: form.expected_delivery_date,
    notes:                  form.notes,
    items: form.items.map(i => ({
      id:               i.id,
      medicine_id:      i.medicine_id,
      quantity_ordered:  i.quantity_ordered,
      unit_price:        i.unit_price,
      discount_percent:  i.discount_percent,
      tax_percent:       i.tax_percent,
    })),
  }

  if (isEditing.value) {
    form.transform(() => payload).put(route('pharmacy.purchase-orders.update', props.editing.id))
  } else {
    form.transform(() => payload).post(route('pharmacy.purchase-orders.store'))
  }
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
</script>