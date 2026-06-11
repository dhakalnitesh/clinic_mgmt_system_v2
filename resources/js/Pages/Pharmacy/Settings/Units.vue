<template>
  <AuthenticatedLayout title="Medicine Units">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Medicine Units</h1>
        <p class="mt-0.5 text-sm text-slate-500">Units of measure used for dispensing and stock</p>
      </div>
      <button @click="openAdd"
              class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        Add Unit
      </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden max-w-2xl">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/80">
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Name</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Abbreviation</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicines</th>
            <th class="px-4 py-3 w-10"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="u in units" :key="u.id" class="hover:bg-slate-50/60 transition-colors">
            <td class="px-4 py-3 font-semibold text-slate-800">{{ u.name }}</td>
            <td class="px-4 py-3 font-mono text-teal-700 font-semibold">{{ u.abbreviation }}</td>
            <td class="px-4 py-3 text-right font-mono text-slate-700">{{ u.medicines_count }}</td>
            <td class="px-4 py-3">
              <ActionMenu>
                <ActionItem @click="openEdit(u)" icon="edit">Edit</ActionItem>
                <ActionDivider />
                <ActionItem @click="deleteUnit(u)" icon="trash" danger>Delete</ActionItem>
              </ActionMenu>
            </td>
          </tr>
          <tr v-if="!units.length">
            <td colspan="4" class="py-12 text-center text-slate-400 text-sm">No units yet</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <Modal v-model="showModal" :title="editing ? 'Edit Unit' : 'Add Unit'" max-width="sm">
      <form @submit.prevent="save" class="space-y-4">
        <FormField label="Unit Name" required :error="form.errors.name">
          <input v-model="form.name" type="text" class="form-input" placeholder="e.g. Tablet" />
        </FormField>
        <FormField label="Abbreviation" required :error="form.errors.abbreviation">
          <input v-model="form.abbreviation" type="text" class="form-input font-mono"
                 placeholder="e.g. Tab" />
        </FormField>
        <div class="flex justify-end gap-3 pt-1">
          <button type="button" @click="showModal = false"
                  class="px-4 py-2 text-sm text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
            Cancel
          </button>
          <button type="submit" :disabled="form.processing"
                  class="px-5 py-2 text-sm font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 disabled:opacity-60 transition">
            {{ editing ? 'Save' : 'Create' }}
          </button>
        </div>
      </form>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal        from '@/Components/Modal.vue'
import FormField    from '@/Components/FormField.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import ActionDivider from '@/Components/ActionDivider.vue'

defineProps({ units: Array })

const showModal = ref(false)
const editing   = ref(null)
const form = useForm({ name: '', abbreviation: '' })

function openAdd()   { editing.value = null; form.reset(); showModal.value = true }
function openEdit(u) {
  editing.value = u
  form.name = u.name; form.abbreviation = u.abbreviation
  showModal.value = true
}
function save() {
  const opts = { onSuccess: () => { showModal.value = false; form.reset() } }
  editing.value
    ? form.put(route('pharmacy.units.update', editing.value.id), opts)
    : form.post(route('pharmacy.units.store'), opts)
}
function deleteUnit(u) {
  if (u.medicines_count > 0) { alert('Cannot delete unit with medicines assigned.'); return }
  if (confirm(`Delete unit "${u.name}"?`)) router.delete(route('pharmacy.units.destroy', u.id), { preserveScroll: true })
}
</script>