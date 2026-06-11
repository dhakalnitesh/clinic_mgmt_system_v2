<template>
  <AuthenticatedLayout title="New Supplier Return">
    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.supplier-returns.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">New Supplier Return</h1>
        <p class="text-sm text-slate-500 mt-0.5">Return expired, damaged or excess stock to supplier</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <FormSection title="Return Details">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <FormField label="Supplier" required :error="form.errors.supplier_id">
            <select v-model="form.supplier_id" @change="loadBatches" class="form-select">
              <option value="">Select supplier…</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </FormField>
          <FormField label="Return Date" required :error="form.errors.return_date">
            <input v-model="form.return_date" type="date" class="form-input" />
          </FormField>
          <FormField label="Reason" required :error="form.errors.reason">
            <select v-model="form.reason" class="form-select">
              <option value="">Select reason…</option>
              <option value="expired">Expired</option>
              <option value="damaged">Damaged</option>
              <option value="excess">Excess Stock</option>
              <option value="wrong_item">Wrong Item</option>
              <option value="quality_issue">Quality Issue</option>
              <option value="other">Other</option>
            </select>
          </FormField>
          <FormField label="Notes" :error="form.errors.notes" class="md:col-span-3">
            <textarea v-model="form.notes" rows="2" class="form-input resize-none" />
          </FormField>
        </div>
      </FormSection>

      <FormSection title="Items to Return"
                   description="Select batches to return from this supplier">

        <!-- Available batches -->
        <div v-if="form.supplier_id && !loadingBatches && availableBatches.length"
             class="mb-4">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
            Available Batches from Supplier
          </p>
          <div class="border border-slate-200 rounded-xl overflow-hidden max-h-64 overflow-y-auto">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 sticky top-0">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Medicine</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Batch</th>
                  <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Expiry</th>
                  <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600">Available</th>
                  <th class="px-3 py-2 w-16"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="batch in availableBatches" :key="batch.id"
                    class="hover:bg-slate-50/60">
                  <td class="px-3 py-2">
                    <div class="font-medium text-slate-800 text-xs">{{ batch.medicine_name }}</div>
                    <div class="text-slate-400 text-xs">{{ batch.strength }}</div>
                  </td>
                  <td class="px-3 py-2 font-mono text-xs">{{ batch.batch_number }}</td>
                  <td class="px-3 py-2">
                    <ExpiryTag :date="batch.expiry_date" :status="batch.expiry_status" :show-days="false" />
                  </td>
                  <td class="px-3 py-2 text-right font-mono text-xs font-semibold">{{ batch.quantity_available }}</td>
                  <td class="px-3 py-2 text-center">
                    <button type="button" @click="addBatch(batch)"
                            :disabled="isAdded(batch.id)"
                            class="text-xs px-2 py-1 rounded bg-teal-50 text-teal-700 border border-teal-200
                                   hover:bg-teal-100 disabled:opacity-40 disabled:cursor-not-allowed transition">
                      {{ isAdded(batch.id) ? 'Added' : 'Add' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-else-if="loadingBatches" class="text-sm text-slate-400 text-center py-4">
          Loading batches…
        </div>
        <div v-else-if="form.supplier_id && !availableBatches.length" class="text-sm text-slate-400 text-center py-4">
          No stock found for this supplier
        </div>
        <div v-else-if="!form.supplier_id" class="text-sm text-slate-400 text-center py-4">
          Select a supplier above to see available batches
        </div>

        <!-- Selected items -->
        <div v-if="form.items.length" class="border border-slate-200 rounded-xl overflow-hidden mt-4">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicine</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Batch</th>
                <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wide w-28">Qty to Return</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Unit Price</th>
                <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Subtotal</th>
                <th class="px-4 py-2.5 w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(item, idx) in form.items" :key="item.stock_batch_id">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">{{ item.medicine_name }}</div>
                  <div class="text-xs text-slate-400">{{ item.strength }}</div>
                </td>
                <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ item.batch_number }}</td>
                <td class="px-4 py-3 text-center">
                  <input v-model.number="item.quantity" type="number" min="1"
                         :max="item.max_quantity" @input="recalc(idx)"
                         class="w-20 text-center font-mono form-input py-1.5" />
                </td>
                <td class="px-4 py-3 text-right font-mono text-xs text-slate-600">
                  {{ formatCurrency(item.unit_price) }}
                </td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-slate-900">
                  {{ formatCurrency(item.subtotal) }}
                </td>
                <td class="px-4 py-3">
                  <button type="button" @click="removeItem(idx)"
                          class="p-1.5 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 transition">
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot class="border-t-2 border-slate-200 bg-slate-50">
              <tr>
                <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Total</td>
                <td class="px-4 py-3 text-right font-mono font-bold text-lg text-teal-700">
                  {{ formatCurrency(grandTotal) }}
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div v-if="!form.items.length && form.supplier_id"
             class="mt-4 rounded-xl border-2 border-dashed border-slate-200 py-8 text-center text-slate-400">
          <p class="text-sm">No items selected — click Add on batches above</p>
        </div>

      </FormSection>

      <div class="flex justify-end gap-3 pt-2 pb-6">
        <Link :href="route('pharmacy.supplier-returns.index')"
              class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing || !form.items.length"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 disabled:opacity-60 transition-all">
          <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
          <CheckIcon v-else class="w-4 h-4" />
          Create Return
        </button>
      </div>

    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon, ArrowPathIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout   from '@/Layouts/AuthenticatedLayout.vue'
import FormSection from '@/Components/FormSection.vue'
import FormField   from '@/Components/FormField.vue'
import ExpiryTag   from '@/Components/Pharmacy/ExpiryTag.vue'

const props = defineProps({ suppliers: Array, today: String, supplier: Object })

const availableBatches = ref([])
const loadingBatches   = ref(false)

const form = useForm({
  supplier_id:  props.supplier?.id ?? '',
  return_date:  props.today,
  reason:       '',
  notes:        '',
  items:        [],
})

const grandTotal = computed(() =>
  form.items.reduce((s, i) => s + (i.subtotal || 0), 0)
)

async function loadBatches() {
  if (!form.supplier_id) { availableBatches.value = []; return }
  loadingBatches.value = true
  try {
    const res = await fetch(
      `/pharmacy/supplier-returns/batches?supplier_id=${form.supplier_id}`,
      { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
    )
    availableBatches.value = await res.json()
  } catch { availableBatches.value = [] }
  finally  { loadingBatches.value = false }
}

function isAdded(batchId) { return form.items.some(i => i.stock_batch_id === batchId) }

function addBatch(batch) {
  form.items.push({
    stock_batch_id: batch.id,
    medicine_id:    batch.medicine_id,
    medicine_name:  batch.medicine_name,
    strength:       batch.strength,
    batch_number:   batch.batch_number,
    max_quantity:   batch.quantity_available,
    quantity:       batch.quantity_available,
    unit_price:     batch.purchase_price,
    subtotal:       Math.round(batch.quantity_available * batch.purchase_price * 100) / 100,
    reason:         '',
  })
}

function removeItem(idx) { form.items.splice(idx, 1) }

function recalc(idx) {
  const i = form.items[idx]
  i.subtotal = Math.round(i.quantity * i.unit_price * 100) / 100
}

function submit() {
  form.post(route('pharmacy.supplier-returns.store'))
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}

// Load batches if supplier pre-selected
if (props.supplier?.id) loadBatches()
</script>