<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    parameter: Object,
    labTests: Array,
})

const form = useForm({
    lab_test_id: props.parameter?.lab_test_id || '',
    name: props.parameter?.name || '',
    unit: props.parameter?.unit || '',
    reference_range: props.parameter?.reference_range || '',
    display_order: props.parameter?.display_order || 1,
    is_active: props.parameter?.is_active ?? true,
})

const submit = () => {
    form.put(route('laboratory.test-parameters.update', props.parameter.id))
}
</script>

<template>
    <Head title="Edit Lab Test Parameter" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-4xl p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Edit Parameter</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Update laboratory test parameter and reference range.
                    </p>
                </div>
                <Link
                    :href="route('laboratory.test-parameters.index')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    <i class="fas fa-arrow-left"></i>
                    Back
                </Link>
            </div>

            <!-- Form -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Lab Test <span class="text-red-500">*</span></label>
                            <select v-model="form.lab_test_id"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Lab Test</option>
                                <option v-for="test in labTests" :key="test.id" :value="test.id">{{ test.name }}</option>
                            </select>
                            <p v-if="form.errors.lab_test_id" class="mt-1 text-xs text-red-500">{{ form.errors.lab_test_id }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Parameter Name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g. Hemoglobin" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Unit</label>
                            <input v-model="form.unit" type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g. g/dL" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Reference Range</label>
                            <input v-model="form.reference_range" type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="e.g. 13.5 - 17.5" />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Display Order</label>
                            <input v-model="form.display_order" type="number" min="1"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div class="flex items-center gap-3 pt-6">
                            <input v-model="form.is_active" type="checkbox" id="is_active" class="h-4 w-4 rounded border-slate-300 text-indigo-600" />
                            <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 border-t border-slate-200 pt-5">
                        <button type="submit" :disabled="form.processing"
                            class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : 'Update Parameter' }}
                        </button>
                        <Link :href="route('laboratory.test-parameters.index')"
                            class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
