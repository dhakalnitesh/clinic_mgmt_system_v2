<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    labTests: Array,
})

const form = useForm({
    lab_test_id: '',
    name: '',
    unit: '',
    reference_range: '',
    display_order: 1,
    is_active: true,
})

const submit = () => {
    form.post(route('laboratory.test-parameters.store'))
}
</script>

<template>
    <Head title="Create Lab Test Parameter" />
    <AuthenticatedLayout>
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Create Lab Test Parameter</h1>
                    <p class="mt-1 text-sm text-slate-500">Add a new laboratory test parameter.</p>
                </div>
                <Link :href="route('laboratory.test-parameters.index')"
                      class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </Link>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="font-semibold text-slate-800">Parameter Information</h2>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Lab Test <span class="text-red-500">*</span></label>
                            <select v-model="form.lab_test_id"
                                    class="form-select w-full"
                                    :class="{ 'border-red-400': form.errors.lab_test_id }">
                                <option value="">Select Lab Test</option>
                                <option v-for="test in labTests" :key="test.id" :value="test.id">{{ test.name }}</option>
                            </select>
                            <p v-if="form.errors.lab_test_id" class="mt-1 text-xs text-red-500">{{ form.errors.lab_test_id }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Parameter Name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Hemoglobin"
                                   class="form-input w-full"
                                   :class="{ 'border-red-400': form.errors.name }" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Unit</label>
                            <input v-model="form.unit" type="text" placeholder="g/dL"
                                   class="form-input w-full" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Reference Range</label>
                            <input v-model="form.reference_range" type="text" placeholder="13 - 17"
                                   class="form-input w-full" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Display Order</label>
                            <input v-model="form.display_order" type="number" min="1"
                                   class="form-input w-full" />
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <input v-model="form.is_active" type="checkbox" id="is_active"
                                   class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                            <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                        <Link :href="route('laboratory.test-parameters.index')"
                              class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 disabled:opacity-60 active:scale-95 transition-all">
                            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                            {{ form.processing ? 'Saving...' : 'Save Parameter' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
