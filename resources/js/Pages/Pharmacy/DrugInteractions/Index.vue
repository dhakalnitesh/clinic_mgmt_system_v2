<template>
  <AuthenticatedLayout title="Drug Interactions">

    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Drug Interactions</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage drug interaction rules checked at dispensing time</p>
      </div>
      <button @click="openAdd"
              class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        Add Interaction
      </button>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <SummaryCard label="Total"           :value="summary.total"           color="slate" icon="check" />
      <SummaryCard label="Contraindicated" :value="summary.contraindicated" color="red"   icon="x-circle"
                   :clickable="true" @click="setSeverity('contraindicated')" />
      <SummaryCard label="Major"           :value="summary.major"           color="orange" icon="warning"
                   :clickable="true" @click="setSeverity('major')" />
      <SummaryCard label="Moderate"        :value="summary.moderate"        color="amber" icon="warning"
                   :clickable="true" @click="setSeverity('moderate')" />
    </div>

    <!-- Severity Legend -->
    <div class="flex items-center gap-4 mb-4 text-xs">
      <span class="font-semibold text-slate-600">Severity:</span>
      <span v-for="s in severities" :key="s.value"
            @click="setSeverity(s.value)"
            :class="[s.class, 'px-2.5 py-1 rounded-full font-semibold cursor-pointer hover:opacity-80 transition']">
        {{ s.label }}
      </span>
      <button v-if="filters.severity" @click="setSeverity('')"
              class="text-slate-400 hover:text-slate-600 underline">Clear</button>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 p-4 flex gap-3">
      <div class="relative flex-1">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
        <input v-model="filters.search" @input="debounceSearch" type="text"
               placeholder="Search drug name…" class="form-input pl-9" />
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Drug A</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Drug B</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Severity</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Description</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Active</th>
              <th class="px-4 py-3 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="interactions.data.length">
              <tr v-for="ia in interactions.data" :key="ia.id"
                  class="hover:bg-slate-50/60 transition-colors">
                <td class="px-4 py-3 font-medium text-slate-800">{{ ia.drug_a }}</td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ ia.drug_b }}</td>
                <td class="px-4 py-3">
                  <span :class="severityBadge(ia.severity)"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide">
                    {{ ia.label }}
                  </span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600 max-w-xs truncate">{{ ia.description }}</td>
                <td class="px-4 py-3">
                  <span :class="ia.is_active ? 'text-emerald-600' : 'text-slate-400'" class="text-xs font-medium">
                    {{ ia.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem @click="openEdit(ia)" icon="edit">Edit</ActionItem>
                    <ActionDivider />
                    <ActionItem @click="deleteInteraction(ia)" icon="trash" danger>Delete</ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="6" class="py-16 text-center text-slate-400">
                <BeakerIcon class="w-12 h-12 mx-auto mb-3 opacity-30" />
                <p class="text-sm">No interactions found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
        <p class="text-xs text-slate-500">{{ interactions.total }} interactions</p>
        <Pagination :links="interactions.links" />
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal v-model="showModal" :title="editing ? 'Edit Interaction' : 'Add Drug Interaction'" max-width="lg">
      <form @submit.prevent="saveInteraction" class="space-y-4">

        <div class="grid grid-cols-2 gap-4" v-if="!editing">
          <FormField label="Drug A (Generic)" required :error="iForm.errors.generic_id_1">
            <Combobox v-model="iForm.generic_id_1" :options="generics" label-key="name" value-key="id"
                      placeholder="Select generic…" />
          </FormField>
          <FormField label="Drug B (Generic)" required :error="iForm.errors.generic_id_2">
            <Combobox v-model="iForm.generic_id_2" :options="generics" label-key="name" value-key="id"
                      placeholder="Select generic…" />
          </FormField>
        </div>

        <div v-else class="grid grid-cols-2 gap-4 pb-1">
          <div class="bg-slate-50 rounded-lg px-3 py-2">
            <p class="text-xs text-slate-500">Drug A</p>
            <p class="font-semibold text-slate-800">{{ editing.drug_a }}</p>
          </div>
          <div class="bg-slate-50 rounded-lg px-3 py-2">
            <p class="text-xs text-slate-500">Drug B</p>
            <p class="font-semibold text-slate-800">{{ editing.drug_b }}</p>
          </div>
        </div>

        <FormField label="Severity" required :error="iForm.errors.severity">
          <div class="grid grid-cols-4 gap-2">
            <button v-for="s in severities" :key="s.value" type="button"
                    @click="iForm.severity = s.value"
                    :class="[
                      'py-2 px-2 rounded-lg text-xs font-bold border transition-all',
                      iForm.severity === s.value ? s.activeClass : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300',
                    ]">
              {{ s.label }}
            </button>
          </div>
        </FormField>

        <FormField label="Description" required :error="iForm.errors.description">
          <textarea v-model="iForm.description" rows="3" class="form-input resize-none"
                    placeholder="What happens when these drugs are combined…" />
        </FormField>

        <FormField label="Clinical Management" :error="iForm.errors.management">
          <textarea v-model="iForm.management" rows="2" class="form-input resize-none"
                    placeholder="How to manage this interaction…" />
        </FormField>

        <ToggleField v-model="iForm.is_active" label="Active"
                     description="Inactive interactions are not checked at dispensing" />

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showModal = false"
                  class="px-4 py-2 text-sm text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
            Cancel
          </button>
          <button type="submit" :disabled="iForm.processing"
                  class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 disabled:opacity-60 transition">
            <ArrowPathIcon v-if="iForm.processing" class="w-4 h-4 animate-spin" />
            {{ editing ? 'Save Changes' : 'Add Interaction' }}
          </button>
        </div>
      </form>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { PlusIcon, MagnifyingGlassIcon, BeakerIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout    from '@/Layouts/AuthenticatedLayout.vue'
import SummaryCard  from '@/Components/SummaryCard.vue'
import Pagination   from '@/Components/Pagination.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import ActionDivider from '@/Components/ActionDivider.vue'
import Modal        from '@/Components/Modal.vue'
import FormField    from '@/Components/FormField.vue'
import ToggleField  from '@/Components/ToggleField.vue'
import Combobox     from '@/Components/Combobox.vue'

const props = defineProps({ interactions: Object, generics: Array, filters: Object, summary: Object })

const filters   = ref({ ...props.filters })
const showModal = ref(false)
const editing   = ref(null)

const severities = [
  { value: 'minor',          label: 'Minor',          class: 'bg-blue-100   text-blue-800',   activeClass: 'bg-blue-600   text-white border-blue-600' },
  { value: 'moderate',       label: 'Moderate',       class: 'bg-amber-100  text-amber-800',  activeClass: 'bg-amber-500  text-white border-amber-500' },
  { value: 'major',          label: 'Major',          class: 'bg-orange-100 text-orange-800', activeClass: 'bg-orange-600 text-white border-orange-600' },
  { value: 'contraindicated',label: 'Contraindicated',class: 'bg-red-100    text-red-800',    activeClass: 'bg-red-700    text-white border-red-700' },
]

const iForm = useForm({
  generic_id_1: '',
  generic_id_2: '',
  severity:     'moderate',
  description:  '',
  management:   '',
  is_active:    true,
})

function applyFilters() {
  router.get(route('pharmacy.drug-interactions.index'), filters.value, { preserveState: true, replace: true })
}
const debounceSearch = useDebounceFn(applyFilters, 350)
function setSeverity(v) { filters.value.severity = v; applyFilters() }

function openAdd() { editing.value = null; iForm.reset(); showModal.value = true }
function openEdit(ia) {
  editing.value = ia
  iForm.severity    = ia.severity
  iForm.description = ia.description
  iForm.management  = ia.management
  iForm.is_active   = ia.is_active
  showModal.value   = true
}

function saveInteraction() {
  if (editing.value) {
    iForm.put(route('pharmacy.drug-interactions.update', editing.value.id), {
      onSuccess: () => { showModal.value = false; iForm.reset() },
    })
  } else {
    iForm.post(route('pharmacy.drug-interactions.store'), {
      onSuccess: () => { showModal.value = false; iForm.reset() },
    })
  }
}

function deleteInteraction(ia) {
  if (confirm(`Delete interaction: ${ia.drug_a} ↔ ${ia.drug_b}?`)) {
    router.delete(route('pharmacy.drug-interactions.destroy', ia.id), { preserveScroll: true })
  }
}

function severityBadge(s) {
  return severities.find(sv => sv.value === s)?.class ?? 'bg-slate-100 text-slate-600'
}
</script>