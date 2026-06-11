<template>
  <AuthenticatedLayout title="Dispensing Counter">

    <!-- Header -->
    <div class="mb-4 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <Link :href="route('pharmacy.sales.index')"
              class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dispensing Counter</h1>
          <p class="text-sm text-slate-500 mt-0.5">{{ today }}</p>
        </div>
      </div>

      <!-- Prescription badge if loaded -->
      <div v-if="prescription" class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
        <ClipboardDocumentListIcon class="w-4 h-4 text-blue-600 shrink-0" />
        <span class="text-sm font-semibold text-blue-700">{{ prescription.prescription_number }}</span>
        <button @click="clearPrescription" class="text-blue-400 hover:text-blue-600 ml-1">
          <XMarkIcon class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- Drug Interaction Alert -->
    <DrugInteractionAlert :interactions="interactions" class="mb-4" />

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

      <!-- LEFT PANEL: drug search + cart (3/5) -->
      <div class="xl:col-span-3 space-y-4">

        <!-- Search -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
          <div class="flex items-center gap-3 mb-3">
            <label class="text-sm font-semibold text-slate-700">Search Medicine</label>
            <span v-if="cart.length" class="text-xs text-slate-400">
              {{ cart.length }} item{{ cart.length !== 1 ? 's' : '' }} in cart
            </span>
          </div>
          <DrugSearchInput
            ref="searchInput"
            placeholder="Scan barcode or type medicine name…"
            :search-url="route('pharmacy.medicines.search')"
            @select="addToCart"
          />
        </div>

        <!-- Prescription Items (if loaded) -->
        <div v-if="prescription && pendingPresItems.length"
             class="bg-blue-50 border border-blue-200 rounded-xl p-4">
          <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-3">
            Pending Prescription Items
          </p>
          <div class="space-y-2">
            <div v-for="item in pendingPresItems" :key="item.id"
                 class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-blue-100">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ item.medicine_name }}</p>
                <p class="text-xs text-slate-400">
                  {{ item.dosage_instruction }}
                  · Qty: <strong>{{ item.pending_quantity }}</strong>
                </p>
              </div>
              <button type="button" @click="addPresItemToCart(item)"
                      class="ml-3 shrink-0 px-3 py-1.5 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                Add
              </button>
            </div>
          </div>
        </div>

        <!-- Cart -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/80 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800">Cart</h3>
            <button v-if="cart.length" @click="clearCart" type="button"
                    class="text-xs text-red-500 hover:text-red-700 underline">Clear cart</button>
          </div>

          <!-- Cart items -->
          <div v-if="cart.length" class="divide-y divide-slate-100">
            <div v-for="(item, idx) in cart" :key="item._key"
                 class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50/40 transition-colors">

              <!-- Index -->
              <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                {{ idx + 1 }}
              </span>

              <!-- Details -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0">
                    <p class="font-semibold text-slate-800 text-sm truncate">{{ item.medicine_name }}</p>
                    <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                      <FormBadge :form="item.form" size="xs" />
                      <span>{{ item.strength }}</span>
                    </p>
                  </div>
                  <button @click="removeFromCart(idx)" type="button"
                          class="shrink-0 p-1 rounded text-slate-300 hover:text-red-500 hover:bg-red-50 transition">
                    <XMarkIcon class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- Controls row -->
                <div class="flex items-center gap-3 mt-2">
                  <!-- Qty stepper -->
                  <div class="flex items-center rounded-lg border border-slate-200 overflow-hidden">
                    <button type="button" @click="decrementQty(idx)"
                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                      <MinusIcon class="w-3.5 h-3.5" />
                    </button>
                    <input v-model.number="item.quantity"
                           @change="onQtyChange(idx)"
                           type="number" min="1"
                           class="w-12 h-8 text-center text-sm font-mono font-semibold border-x border-slate-200 outline-none bg-white" />
                    <button type="button" @click="incrementQty(idx)"
                            class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                      <PlusIcon class="w-3.5 h-3.5" />
                    </button>
                  </div>

                  <!-- Unit price -->
                  <div class="relative w-28">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Rs</span>
                    <input v-model.number="item.unit_price"
                           @input="recalcCartItem(idx)"
                           type="number" min="0" step="0.01"
                           class="w-full pl-7 pr-2 py-1.5 text-sm font-mono border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none" />
                  </div>

                  <!-- Discount % -->
                  <div class="relative w-20">
                    <input v-model.number="item.discount_percent"
                           @input="recalcCartItem(idx)"
                           type="number" min="0" max="100" step="0.01"
                           placeholder="Disc %"
                           class="w-full px-2 py-1.5 text-sm font-mono border border-slate-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none" />
                  </div>

                  <!-- Line total -->
                  <span class="ml-auto font-mono font-bold text-sm text-slate-900 shrink-0">
                    {{ formatCurrency(item.subtotal) }}
                  </span>
                </div>

                <!-- Stock warning -->
                <p v-if="item.insufficient_stock"
                   class="text-xs text-red-600 flex items-center gap-1 mt-1">
                  <ExclamationCircleIcon class="w-3 h-3" />
                  Insufficient stock (available: {{ item.available_stock }})
                </p>

              </div>
            </div>
          </div>

          <!-- Empty cart -->
          <div v-else class="py-16 text-center text-slate-400">
            <ShoppingCartIcon class="w-10 h-10 mx-auto mb-3 opacity-30" />
            <p class="text-sm">Search for a medicine above to start</p>
          </div>
        </div>

      </div>

      <!-- RIGHT PANEL: totals + payment (2/5) -->
      <div class="xl:col-span-2 space-y-4">

        <!-- Sale Details -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 space-y-3">
          <h3 class="text-sm font-semibold text-slate-800">Sale Details</h3>

          <FormField label="Sale Type">
            <select v-model="form.sale_type" class="form-select">
              <option value="counter">Counter (OTC)</option>
              <option value="prescription">Prescription</option>
              <option value="opd">OPD</option>
              <option value="ipd">IPD</option>
            </select>
          </FormField>

          <FormField label="Notes">
            <input v-model="form.notes" type="text" class="form-input"
                   placeholder="Optional notes…" />
          </FormField>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
          <h3 class="text-sm font-semibold text-slate-800 mb-4">Order Summary</h3>

          <dl class="space-y-2 text-sm">
            <div class="flex justify-between text-slate-600">
              <dt>Subtotal</dt>
              <dd class="font-mono">{{ formatCurrency(cartTotals.subtotal) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>
                Discount
                <span v-if="form.discount_value > 0" class="text-xs text-slate-400 ml-1">
                  ({{ form.discount_type === 'percent' ? form.discount_value + '%' : 'Rs ' + form.discount_value }})
                </span>
              </dt>
              <dd class="font-mono text-red-600">- {{ formatCurrency(cartTotals.discountAmount) }}</dd>
            </div>
            <div class="flex justify-between text-slate-600">
              <dt>Tax</dt>
              <dd class="font-mono">{{ formatCurrency(cartTotals.taxAmount) }}</dd>
            </div>
            <div class="flex justify-between font-bold text-slate-900 text-lg border-t border-slate-200 pt-3 mt-2">
              <dt>Total</dt>
              <dd class="font-mono">{{ formatCurrency(cartTotals.total) }}</dd>
            </div>
          </dl>

          <!-- Checkout button -->
          <button type="button"
                  @click="openPayment"
                  :disabled="!cart.length || hasInsufficientStock"
                  class="mt-5 w-full py-3 rounded-xl text-sm font-bold text-white bg-teal-600 shadow-sm hover:bg-teal-700 disabled:opacity-50 active:scale-95 transition-all">
            Proceed to Payment
          </button>

          <p v-if="hasInsufficientStock" class="mt-2 text-xs text-red-600 text-center">
            Some items have insufficient stock
          </p>
          <p v-if="form.errors?.items" class="mt-2 text-xs text-red-600 text-center">
            {{ form.errors.items }}
          </p>
        </div>

      </div>
    </div>

    <!-- Payment Modal -->
    <PaymentModal
      v-model="showPaymentModal"
      :total-amount="cartTotals.total"
      @confirm="onPaymentConfirm"
      @discount-changed="onDiscountChanged"
    />

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import {
  ArrowLeftIcon, PlusIcon, MinusIcon, XMarkIcon,
  ShoppingCartIcon, ClipboardDocumentListIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import FormField           from '@/Components/FormField.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormBadge           from '@/Components/Pharmacy/FormBadge.vue'
import DrugSearchInput     from '@/Components/Pharmacy/DrugSearchInput.vue'
import DrugInteractionAlert from '@/Components/Pharmacy/DrugInteractionAlert.vue'
import PaymentModal        from '@/Components/Pharmacy/PaymentModal.vue'

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
  today:        { type: String, default: '' },
  prescription: { type: Object, default: null },
})

// ── State ──────────────────────────────────────────────────────────
let keyCounter         = 0
const cart             = ref([])
const interactions     = ref([])
const showPaymentModal = ref(false)
const searchInput      = ref(null)

const form = useForm({
  patient_id:        props.prescription?.patient_id ?? null,
  prescription_id:   props.prescription?.id         ?? null,
  sale_date:         props.today,
  sale_type:         'counter',
  discount_type:     'percent',
  discount_value:    0,
  payment_mode:      'cash',
  payment_reference: '',
  paid_amount:       0,
  notes:             '',
  items:             [],
})

// ── Prescription ───────────────────────────────────────────────────
const pendingPresItems = computed(() =>
  (props.prescription?.items ?? []).filter(i => i.pending_quantity > 0)
)

function clearPrescription() {
  router.get(route('pharmacy.sales.create'))
}

function addPresItemToCart(presItem) {
  if (!presItem.medicine_id) return
  addToCart({
    id:              presItem.medicine_id,
    name:            presItem.medicine_name,
    strength:        presItem.strength,
    form:            presItem.form,
    unit:            presItem.unit,
    sale_price:      0,
    tax_percent:     0,
    total_stock:     presItem.pending_quantity + 99,
    stock_status:    'in_stock',
    _prescription_item_id: presItem.id,
    _quantity:       presItem.pending_quantity,
  })
}

// ── Cart Logic ─────────────────────────────────────────────────────
function addToCart(med) {
  const existing = cart.value.find(i => i.medicine_id === med.id)
  if (existing) {
    existing.quantity++
    recalcCartItem(cart.value.indexOf(existing))
    checkInteractions()
    return
  }

  cart.value.push({
    _key:                keyCounter++,
    medicine_id:         med.id,
    medicine_name:       med.name,
    strength:            med.strength,
    form:                med.form,
    unit:                med.unit,
    unit_price:          parseFloat(med.sale_price ?? 0),
    quantity:            med._quantity ?? 1,
    discount_percent:    0,
    tax_percent:         parseFloat(med.tax_percent ?? 0),
    subtotal:            0,
    available_stock:     med.total_stock,
    insufficient_stock:  false,
    prescription_item_id: med._prescription_item_id ?? null,
  })
  recalcCartItem(cart.value.length - 1)
  checkInteractions()
}

function removeFromCart(idx) {
  cart.value.splice(idx, 1)
  checkInteractions()
}

function clearCart() { cart.value = []; interactions.value = [] }

function incrementQty(idx) {
  cart.value[idx].quantity++
  onQtyChange(idx)
}

function decrementQty(idx) {
  if (cart.value[idx].quantity <= 1) return
  cart.value[idx].quantity--
  onQtyChange(idx)
}

function onQtyChange(idx) {
  const item = cart.value[idx]
  item.insufficient_stock = item.quantity > item.available_stock
  recalcCartItem(idx)
}

function recalcCartItem(idx) {
  const i    = cart.value[idx]
  const gross = i.quantity * i.unit_price
  const disc  = gross * ((i.discount_percent || 0) / 100)
  const after = gross - disc
  i.subtotal  = Math.round((after + (after * (i.tax_percent || 0) / 100)) * 100) / 100
}

// ── Totals ─────────────────────────────────────────────────────────
const cartTotals = computed(() => {
  const subtotal    = cart.value.reduce((s, i) => s + (i.subtotal || 0), 0)
  const taxAmount   = cart.value.reduce((s, i) => {
    const gross  = i.quantity * i.unit_price
    const after  = gross - (gross * (i.discount_percent || 0) / 100)
    return s + after * (i.tax_percent || 0) / 100
  }, 0)

  let discountAmount = 0
  if (form.discount_type === 'percent') {
    discountAmount = subtotal * (form.discount_value / 100)
  } else {
    discountAmount = form.discount_value
  }

  return {
    subtotal,
    taxAmount:      Math.round(taxAmount * 100) / 100,
    discountAmount: Math.round(discountAmount * 100) / 100,
    total:          Math.round((subtotal - discountAmount) * 100) / 100,
  }
})

const hasInsufficientStock = computed(() =>
  cart.value.some(i => i.insufficient_stock)
)

// ── Drug Interaction Check ─────────────────────────────────────────
let interactionTimer = null
function checkInteractions() {
  clearTimeout(interactionTimer)
  if (cart.value.length < 2) { interactions.value = []; return }

  interactionTimer = setTimeout(async () => {
    try {
      const res = await fetch(route('pharmacy.sales.check-interactions'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ medicine_ids: cart.value.map(i => i.medicine_id) }),
      })
      interactions.value = await res.json()
    } catch { interactions.value = [] }
  }, 600)
}

// ── Payment ────────────────────────────────────────────────────────
function openPayment() { showPaymentModal.value = true }

function onDiscountChanged({ type, value }) {
  form.discount_type  = type
  form.discount_value = value
}

function onPaymentConfirm(paymentData) {
  form.payment_mode      = paymentData.payment_mode
  form.payment_reference = paymentData.payment_reference
  form.paid_amount       = paymentData.paid_amount
  form.discount_type     = paymentData.discount_type
  form.discount_value    = paymentData.discount_value

  const payload = {
    patient_id:        form.patient_id,
    prescription_id:   form.prescription_id,
    sale_date:         form.sale_date,
    sale_type:         form.sale_type,
    discount_type:     form.discount_type,
    discount_value:    form.discount_value,
    payment_mode:      form.payment_mode,
    payment_reference: form.payment_reference,
    paid_amount:       form.paid_amount,
    notes:             form.notes,
    items: cart.value.map(i => ({
      medicine_id:          i.medicine_id,
      quantity:             i.quantity,
      unit_price:           i.unit_price,
      discount_percent:     i.discount_percent,
      tax_percent:          i.tax_percent,
      prescription_item_id: i.prescription_item_id,
    })),
  }

  form.transform(() => payload).post(route('pharmacy.sales.store'))
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
</script>