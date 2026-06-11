<template>
  <Modal :model-value="modelValue" title="Payment" max-width="sm" @update:model-value="$emit('update:modelValue', $event)">
    <div class="space-y-5">

      <!-- Total due -->
      <div class="rounded-xl bg-teal-50 border border-teal-200 p-4 text-center">
        <p class="text-xs font-semibold text-teal-600 uppercase tracking-wide mb-1">Total Due</p>
        <p class="text-3xl font-bold font-mono text-teal-800">{{ formatCurrency(totalAmount) }}</p>
      </div>

      <!-- Payment Mode -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Payment Method</label>
        <div class="grid grid-cols-3 gap-2">
          <button v-for="mode in paymentModes" :key="mode.value" type="button"
                  @click="form.payment_mode = mode.value"
                  :class="[
                    'flex flex-col items-center gap-1 py-3 px-2 rounded-xl border-2 text-xs font-semibold transition-all',
                    form.payment_mode === mode.value
                      ? 'border-teal-500 bg-teal-50 text-teal-700'
                      : 'border-slate-200 text-slate-600 hover:border-slate-300',
                  ]">
            <span class="text-lg">{{ mode.icon }}</span>
            {{ mode.label }}
          </button>
        </div>
      </div>

      <!-- Payment Reference (card / UPI) -->
      <div v-if="['card','upi','bank_transfer'].includes(form.payment_mode)">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
          {{ form.payment_mode === 'card' ? 'Card / Transaction No.' : 'Reference No.' }}
        </label>
        <input v-model="form.payment_reference" type="text"
               class="form-input font-mono" placeholder="e.g. TXN123456" />
      </div>

      <!-- Amount Tendered -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Amount Tendered (Rs)</label>
        <input v-model.number="form.paid_amount" type="number" :min="totalAmount" step="0.01"
               class="form-input font-mono text-lg font-bold text-center"
               @input="calcChange" />
      </div>

      <!-- Change -->
      <div v-if="form.payment_mode === 'cash'" class="rounded-lg bg-slate-50 border border-slate-200 p-3 flex justify-between items-center">
        <span class="text-sm text-slate-600">Change to return</span>
        <span class="font-mono font-bold text-lg"
              :class="changeAmount >= 0 ? 'text-emerald-700' : 'text-red-600'">
          {{ formatCurrency(changeAmount) }}
        </span>
      </div>

      <!-- Discount -->
      <div class="border-t border-slate-100 pt-4">
        <div class="flex items-center justify-between mb-2">
          <label class="text-sm font-medium text-slate-700">Discount</label>
          <div class="flex rounded-lg border border-slate-200 overflow-hidden text-xs">
            <button type="button" @click="form.discount_type = 'percent'"
                    :class="['px-3 py-1.5 font-semibold transition', form.discount_type === 'percent' ? 'bg-teal-600 text-white' : 'text-slate-600 hover:bg-slate-50']">%</button>
            <button type="button" @click="form.discount_type = 'amount'"
                    :class="['px-3 py-1.5 font-semibold transition', form.discount_type === 'amount' ? 'bg-teal-600 text-white' : 'text-slate-600 hover:bg-slate-50']">Rs</button>
          </div>
        </div>
        <input v-model.number="form.discount_value" type="number" min="0" step="0.01"
               class="form-input font-mono"
               :placeholder="form.discount_type === 'percent' ? '0 %' : '0.00 Rs'"
               @input="$emit('discount-changed', { type: form.discount_type, value: form.discount_value })" />
      </div>

      <!-- Confirm -->
      <div class="flex gap-3 pt-1">
        <button type="button" @click="$emit('update:modelValue', false)"
                class="flex-1 px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
          Cancel
        </button>
        <button type="button" @click="confirm"
                :disabled="!canConfirm"
                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-xl shadow-sm hover:bg-teal-700 disabled:opacity-50 transition">
          Confirm Payment
        </button>
      </div>

    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  modelValue:  { type: Boolean, default: false },
  totalAmount: { type: Number,  default: 0 },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'discount-changed'])

const form = ref({
  payment_mode:      'cash',
  payment_reference: '',
  paid_amount:       props.totalAmount,
  discount_type:     'percent',
  discount_value:    0,
})

const paymentModes = [
  { value: 'cash',          label: 'Cash',     icon: '💵' },
  { value: 'card',          label: 'Card',     icon: '💳' },
  { value: 'upi',           label: 'UPI',      icon: '📱' },
  { value: 'insurance',     label: 'Insurance',icon: '🏥' },
  { value: 'credit',        label: 'Credit',   icon: '📋' },
  { value: 'bank_transfer', label: 'Bank',     icon: '🏦' },
]

const changeAmount = computed(() =>
  Math.max(0, (form.value.paid_amount ?? 0) - props.totalAmount)
)

const canConfirm = computed(() =>
  form.value.payment_mode &&
  (form.value.payment_mode !== 'cash' || (form.value.paid_amount ?? 0) >= props.totalAmount)
)

// Sync paid_amount when totalAmount changes
watch(() => props.totalAmount, (val) => {
  if (form.value.paid_amount < val) form.value.paid_amount = val
})

function calcChange() {
  // just a reactivity trigger — changeAmount computed handles it
}

function confirm() {
  if (! canConfirm.value) return
  emit('confirm', { ...form.value, change_amount: changeAmount.value })
  emit('update:modelValue', false)
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR' }).format(v ?? 0)
}
</script>