<template>
  <AuthenticatedLayout title="Medicine Categories">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Medicine Categories</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage drug classification categories</p>
      </div>
      <button @click="openAdd"
              class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 active:scale-95 transition-all">
        <PlusIcon class="w-4 h-4" />
        Add Category
      </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/80">
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Category</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Description</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Medicines</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
            <th class="px-4 py-3 w-10"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50/60 transition-colors">
            <td class="px-4 py-3 font-semibold text-slate-800">{{ cat.name }}</td>
            <td class="px-4 py-3 text-slate-500 text-xs max-w-xs truncate">{{ cat.description ?? '—' }}</td>
            <td class="px-4 py-3 text-right font-mono text-slate-700">{{ cat.medicines_count }}</td>
            <td class="px-4 py-3">
              <span :class="cat.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold">
                {{ cat.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <ActionMenu>
                <ActionItem @click="openEdit(cat)" icon="edit">Edit</ActionItem>
                <ActionDivider />
                <ActionItem @click="deleteCategory(cat)" icon="trash" danger>Delete</ActionItem>
              </ActionMenu>
            </td>
          </tr>
          <tr v-if="!categories.length">
            <td colspan="5" class="py-12 text-center text-slate-400 text-sm">No categories yet</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Modal -->
    <Modal v-model="showModal" :title="editing ? 'Edit Category' : 'Add Category'" max-width="md">
      <form @submit.prevent="save" class="space-y-4">
        <FormField label="Name" required :error="form.errors.name">
          <input v-model="form.name" type="text" class="form-input" placeholder="e.g. Antibiotics" />
        </FormField>
        <FormField label="Description" :error="form.errors.description">
          <textarea v-model="form.description" rows="2" class="form-input resize-none" />
        </FormField>
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
import Modal        from '@/Components/Modal.vue'
import FormField    from '@/Components/FormField.vue'
import ToggleField  from '@/Components/ToggleField.vue'
import ActionMenu   from '@/Components/ActionMenu.vue'
import ActionItem   from '@/Components/ActionItem.vue'
import ActionDivider from '@/Components/ActionDivider.vue'

defineProps({ categories: Array })

const showModal = ref(false)
const editing   = ref(null)
const form = useForm({ name: '', description: '', is_active: true })

function openAdd()    { editing.value = null; form.reset(); showModal.value = true }
function openEdit(c)  {
  editing.value = c
  form.name = c.name; form.description = c.description ?? ''; form.is_active = c.is_active
  showModal.value = true
}
function save() {
  if (editing.value) {
    form.put(route('pharmacy.categories.update', editing.value.id), { onSuccess: () => { showModal.value = false } })
  } else {
    form.post(route('pharmacy.categories.store'), { onSuccess: () => { showModal.value = false; form.reset() } })
  }
}
function deleteCategory(c) {
  if (c.medicines_count > 0) { alert('Cannot delete category with medicines.'); return }
  if (confirm(`Delete category "${c.name}"?`)) {
    router.delete(route('pharmacy.categories.destroy', c.id), { preserveScroll: true })
  }
}
</script>