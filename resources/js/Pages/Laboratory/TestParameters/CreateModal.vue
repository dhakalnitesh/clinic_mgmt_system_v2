<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    labTests: Array,
})

const emit = defineEmits(['close', 'success'])

const localErrors = ref({})
const displayErrors = computed(() => {
    return { ...usePage().props.errors, ...localErrors.value }
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
    form.post(route('laboratory.test-parameters.store'), {
        preserveScroll: true,
        onError: (err) => {
            localErrors.value = err
        },
        onSuccess: () => {
            emit('success')
            emit('close')
        },
    })
}
</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
        <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-indigo-600 flex items-center gap-2">
                    <i class="fas fa-flask"></i>
                    Add Test Parameter
                </h2>
                <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 text-2xl font-bold leading-none">&times;</button>
            </div>

            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto space-y-4">
                <div v-if="displayErrors.lab_test_id || displayErrors.name || displayErrors.error"
                     class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                    <p v-if="displayErrors.error" class="text-sm text-red-600">{{ displayErrors.error }}</p>
                    <p v-if="displayErrors.lab_test_id" class="text-sm text-red-600">{{ displayErrors.lab_test_id }}</p>
                    <p v-if="displayErrors.name" class="text-sm text-red-600">{{ displayErrors.name }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Lab Test <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.lab_test_id"
                                class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"
                                :class="{ 'border-red-400': displayErrors.lab_test_id }">
                            <option value="">Select Lab Test</option>
                            <option v-for="test in labTests" :key="test.id" :value="test.id">{{ test.name }}</option>
                        </select>
                        <p v-if="displayErrors.lab_test_id" class="text-xs text-red-500 mt-0.5">{{ displayErrors.lab_test_id }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Parameter Name <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.name" type="text" placeholder="Hemoglobin"
                               class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition"
                               :class="{ 'border-red-400': displayErrors.name }" />
                        <p v-if="displayErrors.name" class="text-xs text-red-500 mt-0.5">{{ displayErrors.name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Unit</label>
                        <input v-model="form.unit" type="text" placeholder="g/dL"
                               class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Reference Range</label>
                        <input v-model="form.reference_range" type="text" placeholder="13 - 17"
                               class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Display Order</label>
                        <input v-model.number="form.display_order" type="number" min="1"
                               class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div class="flex items-center gap-3 pt-7">
                        <input v-model="form.is_active" type="checkbox" id="is_active"
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200">
                <button type="button" @click="$emit('close')"
                        class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm font-medium transition">
                    Cancel
                </button>
                <button type="button" :disabled="form.processing" @click="submit"
                        class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 text-sm font-medium transition">
                    <i v-if="form.processing" class="fas fa-spinner fa-spin mr-1"></i>
                    {{ form.processing ? 'Saving...' : 'Save Parameter' }}
                </button>
            </div>
        </div>
    </div>
</template>
