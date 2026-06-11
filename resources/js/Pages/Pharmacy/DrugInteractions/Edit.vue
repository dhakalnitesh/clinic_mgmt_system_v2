<template>
  <AuthenticatedLayout title="Edit Drug Interaction">

    <div class="mb-6 flex items-center gap-3">
      <Link :href="route('pharmacy.drug-interactions.index')"
            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
        <ArrowLeftIcon class="w-5 h-5" />
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Drug Interaction</h1>
        <p class="text-sm text-slate-500 mt-0.5">Update interaction rule details</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6">

      <FormSection title="Drug Pair" description="Select the two interacting generic drugs">

        <FormField label="Generic Drug A" required :error="form.errors.generic_id_1">
          <select v-model="form.generic_id_1" class="form-select">
            <option value="">Select generic…</option>
            <option v-for="g in generics" :key="g.id" :value="g.id">{{ g.name }}</option>
          </select>
        </FormField>

        <FormField label="Generic Drug B" required :error="form.errors.generic_id_2">
          <select v-model="form.generic_id_2" class="form-select">
            <option value="">Select generic…</option>
            <option v-for="g in generics" :key="g.id" :value="g.id">{{ g.name }}</option>
          </select>
        </FormField>

        <FormField label="Severity" required :error="form.errors.severity">
          <select v-model="form.severity" class="form-select">
            <option value="minor">Minor</option>
            <option value="moderate">Moderate</option>
            <option value="major">Major</option>
            <option value="severe">Severe</option>
            <option value="contraindicated">Contraindicated</option>
          </select>
        </FormField>
      </FormSection>

      <FormSection title="Details" description="Clinical description and management guidance">

        <FormField label="Description" required :error="form.errors.description">
          <textarea v-model="form.description" rows="3" placeholder="Describe the interaction effect…"
                    class="form-input" :class="{ 'form-input-error': form.errors.description }"></textarea>
        </FormField>

        <FormField label="Management" :error="form.errors.management">
          <textarea v-model="form.management" rows="2" placeholder="Clinical management guidance…"
                    class="form-input"></textarea>
        </FormField>

        <FormField label="Reference" :error="form.errors.reference">
          <input v-model="form.reference" type="text" placeholder="Literature reference…"
                 class="form-input" />
        </FormField>

        <label class="flex items-center gap-2 cursor-pointer">
          <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </FormSection>

      <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 disabled:opacity-70 transition">
          <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
          <span v-else>Update Interaction</span>
        </button>
        <Link :href="route('pharmacy.drug-interactions.index')"
              class="text-sm font-medium text-slate-500 hover:text-slate-700 transition">Cancel</Link>
      </div>
    </form>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  interaction: Object,
  generics: Array,
})

const form = useForm({
  generic_id_1: props.interaction?.generic_id_1 ?? '',
  generic_id_2: props.interaction?.generic_id_2 ?? '',
  severity: props.interaction?.severity ?? 'moderate',
  description: props.interaction?.description ?? '',
  management: props.interaction?.management ?? '',
  reference: props.interaction?.reference ?? '',
  is_active: props.interaction?.is_active ?? true,
})

const submit = () => {
  form.put(route('pharmacy.drug-interactions.update', props.interaction.id))
}
</script>
