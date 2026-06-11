<template>
  <AuthenticatedLayout title="Generic Names">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Generic Names</h1>
        <p class="mt-0.5 text-sm text-slate-500">International Non-proprietary Names (INN) used for interaction checking</p>
      </div>
      <button @click="openAdd"
              class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        Add Generic
      </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Generic Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Pharmacological Class</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicines</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Flags</th>
              <th class="px-4 py-3 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-if="generics.data.length">
              <tr v-for="g in generics.data" :key="g.id" class="hover:bg-slate-50/60 transition-colors">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ g.name }}</td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ g.pharmacological_class ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-mono text-slate-700">{{ g.medicines_count }}</td>
                <td class="px-4 py-3 flex items-center gap-1.5">
                  <span v-if="g.is_controlled"
                        class="px-2 py-0.5 rounded text-xs font-semibold bg-red-50 text-red-700">CD</span>
                  <span v-if="!g.is_active"
                        class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-500">Inactive</span>
                </td>
                <td class="px-4 py-3">
                  <ActionMenu>
                    <ActionItem @click="openEdit(g)" icon="edit">Edit</ActionItem>
                    <ActionDivider />
                    <ActionItem @click="deleteGeneric(g)" icon="trash" danger>Delete</ActionItem>
                  </ActionMenu>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="5" class="py-12 text-center text-slate-400 text-sm">No generics found</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="border-t border-slate-200 px-4 py-3 flex justify-between items-center">
        <p class="text-xs text-slate-500">{{ generics.total }} generics</p>
        <Pagination :links="generics.links" />
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal v-model="showModal" :title="editing ? 'Edit Generic' : 'Add Generic'" max-width="md">
      <form @submit.prevent="save" class="space-y-4">
        <FormField label="Generic Name (INN)" required :error="form.errors.name">
          <input v-model="form.name" type="text" class="form-input" placeholder="e.g. Amoxicillin" />
        </FormField>
        <FormField label="Pharmacological Class" :error="form.errors.pharmacological_class">
          <input v-model="form.pharmacological_class" type="text" class="form-input"
                 placeholder="e.g. Penicillin Antibiotic" />
        </FormField>
        <FormField label="Description" :error="form.errors.description">
          <textarea v-model="form.description" rows="2" class="form-input resize-none" />
        </FormField>
        <ToggleField v-model="form.is_controlled" label="Controlled Substance (CD)"
                     description="Flag as a Schedule/controlled drug" />
        <ToggleField v-model="form.is_active" label="Active" />
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
import Pagination   from '@/Components/Pagination.vue'
import Modal        from '@/Components/Modal.vue'
import FormField    from '@/Components/FormField.vue'
import ToggleField  from '@/Components/ToggleField.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import ActionDivider from '@/Components/ActionDivider.vue'

defineProps({ generics: Object })

const showModal = ref(false)
const editing   = ref(null)
const form = useForm({ name:'', pharmacological_class:'', description:'', is_controlled: false, is_active: true })

function openAdd()   { editing.value = null; form.reset(); showModal.value = true }
function openEdit(g) {
  editing.value = g
  Object.assign(form, { name: g.name, pharmacological_class: g.pharmacological_class ?? '',
    description: g.description ?? '', is_controlled: g.is_controlled, is_active: g.is_active })
  showModal.value = true
}
function save() {
  const opts = { onSuccess: () => { showModal.value = false; form.reset() } }
  editing.value
    ? form.put(route('pharmacy.generics.update', editing.value.id), opts)
    : form.post(route('pharmacy.generics.store'), opts)
}
function deleteGeneric(g) {
  if (g.medicines_count > 0) { alert('Cannot delete generic with medicines assigned.'); return }
  if (confirm(`Delete "${g.name}"?`)) router.delete(route('pharmacy.generics.destroy', g.id), { preserveScroll: true })
}
</script>