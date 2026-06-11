<template>
  <AuthenticatedLayout title="Pharmacy Dashboard">

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pharmacy Dashboard</h1>
        <p class="mt-0.5 text-sm text-slate-500">{{ formatDate(new Date()) }} · Overview</p>
      </div>
      <Link :href="route('pharmacy.sales.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        New Sale
      </Link>
    </div>

    <!-- KPI Cards Row 1 -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Today's Sales</p>
        <p class="mt-1 text-2xl font-bold text-slate-900 font-mono">{{ kpis.today_sales_count }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ formatCurrency(kpis.today_revenue) }} revenue</p>
      </div>
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Month Revenue</p>
        <p class="mt-1 text-2xl font-bold text-teal-700 font-mono">{{ formatCurrency(kpis.month_revenue) }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ kpis.month_sales_count }} invoices</p>
      </div>
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Stock Value</p>
        <p class="mt-1 text-2xl font-bold text-slate-900 font-mono">{{ formatCurrency(kpis.stock_value) }}</p>
        <p class="text-xs text-slate-400 mt-1">At purchase price</p>
      </div>
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Pending Work</p>
        <div class="mt-2 space-y-1">
          <div class="flex justify-between text-xs">
            <span class="text-slate-500">Prescriptions</span>
            <span class="font-mono font-bold" :class="kpis.pending_prescriptions > 0 ? 'text-amber-600' : 'text-slate-400'">
              {{ kpis.pending_prescriptions }}
            </span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-slate-500">Pending GRNs</span>
            <span class="font-mono font-bold" :class="kpis.pending_grns > 0 ? 'text-amber-600' : 'text-slate-400'">
              {{ kpis.pending_grns }}
            </span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-slate-500">Pending POs</span>
            <span class="font-mono font-bold" :class="kpis.pending_pos > 0 ? 'text-blue-600' : 'text-slate-400'">
              {{ kpis.pending_pos }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Alert Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <Link :href="route('pharmacy.inventory.index') + '?stock=low'"
            class="bg-amber-50 border border-amber-200 rounded-xl p-4 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Low Stock</p>
          <ExclamationTriangleIcon class="w-4 h-4 text-amber-500 group-hover:scale-110 transition" />
        </div>
        <p class="mt-1 text-2xl font-bold text-amber-800 font-mono">{{ kpis.low_stock_count }}</p>
        <p class="text-xs text-amber-600 mt-1">medicines below reorder level</p>
      </Link>

      <Link :href="route('pharmacy.inventory.index') + '?stock=out'"
            class="bg-red-50 border border-red-200 rounded-xl p-4 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Out of Stock</p>
          <XCircleIcon class="w-4 h-4 text-red-500 group-hover:scale-110 transition" />
        </div>
        <p class="mt-1 text-2xl font-bold text-red-800 font-mono">{{ kpis.out_of_stock_count }}</p>
        <p class="text-xs text-red-600 mt-1">medicines with zero stock</p>
      </Link>

      <Link :href="route('pharmacy.reports.expiry')"
            class="bg-orange-50 border border-orange-200 rounded-xl p-4 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Near Expiry</p>
          <ClockIcon class="w-4 h-4 text-orange-500 group-hover:scale-110 transition" />
        </div>
        <p class="mt-1 text-2xl font-bold text-orange-800 font-mono">{{ kpis.near_expiry_count }}</p>
        <p class="text-xs text-orange-600 mt-1">batches expiring in 90 days</p>
      </Link>

      <Link :href="route('pharmacy.reports.expiry') + '?filter=expired'"
            class="bg-red-50 border border-red-300 rounded-xl p-4 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <p class="text-xs font-semibold text-red-700 uppercase tracking-wide">Expired In Stock</p>
          <ExclamationCircleIcon class="w-4 h-4 text-red-600 group-hover:scale-110 transition" />
        </div>
        <p class="mt-1 text-2xl font-bold text-red-900 font-mono">{{ kpis.expired_in_stock }}</p>
        <p class="text-xs text-red-700 mt-1">batches — requires write-off</p>
      </Link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Sales Chart -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-slate-800 mb-4">Sales — Last 7 Days</h3>
        <div class="flex items-end justify-between gap-2 h-32">
          <div v-for="day in last7_days" :key="day.date"
               class="flex-1 flex flex-col items-center gap-1">
            <div class="w-full bg-teal-100 rounded-t-md relative group cursor-default"
                 :style="{ height: barHeight(day.revenue) + 'px' }">
              <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded
                          opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none z-10">
                {{ formatCurrency(day.revenue) }}
              </div>
              <div class="w-full bg-teal-500 rounded-t-md absolute bottom-0"
                   :style="{ height: barHeight(day.revenue) + 'px' }" />
            </div>
            <span class="text-xs text-slate-400">{{ day.date }}</span>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
          <span>{{ formatCurrency(Math.min(...last7_days.map(d => d.revenue))) }} min</span>
          <span>{{ formatCurrency(Math.max(...last7_days.map(d => d.revenue))) }} max</span>
        </div>
      </div>

      <!-- Top Medicines + Near Expiry -->
      <div class="space-y-5">

        <!-- Top Medicines -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <h3 class="text-sm font-semibold text-slate-800 mb-3">Top Selling This Month</h3>
          <div class="space-y-2">
            <div v-for="(med, idx) in top_medicines" :key="med.medicine"
                 class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-2 min-w-0">
                <span class="w-5 h-5 rounded-full bg-teal-100 text-teal-700 text-xs font-bold
                             flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
                <span class="truncate text-slate-700 font-medium">{{ med.medicine }}</span>
              </div>
              <span class="font-mono text-xs text-slate-500 shrink-0 ml-2">{{ med.qty_sold }} units</span>
            </div>
            <div v-if="!top_medicines.length" class="text-xs text-slate-400 text-center py-3">
              No sales this month
            </div>
          </div>
        </div>

        <!-- Near Expiry Items -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-800">Expiring Soon (30d)</h3>
            <Link :href="route('pharmacy.reports.expiry')"
                  class="text-xs text-teal-600 hover:underline">View all</Link>
          </div>
          <div class="space-y-2">
            <div v-for="item in near_expiry_items" :key="item.batch_number"
                 class="flex items-start justify-between text-xs">
              <div class="min-w-0">
                <p class="font-medium text-slate-700 truncate">{{ item.medicine }}</p>
                <p class="font-mono text-slate-400">{{ item.batch_number }}</p>
              </div>
              <div class="text-right shrink-0 ml-2">
                <p class="font-mono font-bold"
                   :class="item.days_to_expiry <= 7 ? 'text-red-600' : 'text-amber-600'">
                  {{ item.days_to_expiry }}d
                </p>
                <p class="text-slate-400">qty: {{ item.quantity_available }}</p>
              </div>
            </div>
            <div v-if="!near_expiry_items.length" class="text-xs text-slate-400 text-center py-3">
              No items expiring soon
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Quick Links -->
    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <QuickLink :href="route('pharmacy.sales.create')"      icon="💊" label="New Sale" />
      <QuickLink :href="route('pharmacy.prescriptions.index')" icon="📋" label="Prescriptions" />
      <QuickLink :href="route('pharmacy.purchase-orders.create')" icon="📦" label="New PO" />
      <QuickLink :href="route('pharmacy.grn.index')"          icon="📥" label="GRN List" />
      <QuickLink :href="route('pharmacy.inventory.index')"    icon="🏪" label="Inventory" />
      <QuickLink :href="route('pharmacy.reports.index')"      icon="📊" label="Reports" />
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  PlusIcon,
  ExclamationTriangleIcon, XCircleIcon,
  ClockIcon, ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const QuickLink = {
  props: ['href', 'icon', 'label'],
  template: `
    <a :href="href"
       class="flex flex-col items-center gap-1.5 p-3 bg-white rounded-xl border border-slate-200
              shadow-sm hover:shadow-md hover:border-teal-300 transition-all text-center group">
      <span class="text-2xl group-hover:scale-110 transition">{{ icon }}</span>
      <span class="text-xs font-medium text-slate-600">{{ label }}</span>
    </a>`,
}

const props = defineProps({
  kpis:             Object,
  last7_days:       Array,
  top_medicines:    Array,
  near_expiry_items:Array,
})

const maxRevenue = computed(() =>
  Math.max(...props.last7_days.map(d => d.revenue), 1)
)

function barHeight(revenue) {
  return Math.max(4, Math.round((revenue / maxRevenue.value) * 96))
}

function formatCurrency(v) {
  return new Intl.NumberFormat('en-NP', { style: 'currency', currency: 'NPR', maximumFractionDigits: 0 }).format(v ?? 0)
}
function formatDate(d) {
  return d.toLocaleDateString('en-GB', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}
</script>