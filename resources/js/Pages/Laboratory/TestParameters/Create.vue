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

        <div class="mx-auto max-w-4xl p-6">

            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Create Lab Test Parameter
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Add a new laboratory test parameter.
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

            <!-- Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <!-- Card Header -->
                <div class="border-b border-slate-200 px-6 py-4">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">

                            <i class="fas fa-vial"></i>

                        </div>

                        <div>

                            <h2 class="font-semibold text-slate-800">
                                Parameter Information
                            </h2>

                            <p class="text-sm text-slate-500">
                                Enter the details below.
                            </p>

                        </div>

                    </div>

                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6">

                    <div class="grid gap-6 md:grid-cols-2">

                        <!-- Lab Test -->
                        <div>

                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Lab Test <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="form.lab_test_id"
                                class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">
                                    Select Lab Test
                                </option>

                                <option
                                    v-for="test in labTests"
                                    :key="test.id"
                                    :value="test.id"
                                >
                                    {{ test.name }}
                                </option>

                            </select>

                            <div
                                v-if="form.errors.lab_test_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.lab_test_id }}
                            </div>

                        </div>

                        <!-- Parameter Name -->
                        <div>

                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Parameter Name <span class="text-red-500">*</span>
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Hemoglobin"
                                class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            />

                            <div
                                v-if="form.errors.name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.name }}
                            </div>

                        </div>

                        <!-- Unit -->
                        <div>

                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Unit
                            </label>

                            <input
                                v-model="form.unit"
                                type="text"
                                placeholder="g/dL"
                                class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            />

                            <div
                                v-if="form.errors.unit"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.unit }}
                            </div>

                        </div>

                        <!-- Reference Range -->
                        <div>

                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Reference Range
                            </label>

                            <input
                                v-model="form.reference_range"
                                type="text"
                                placeholder="13 - 17"
                                class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            />

                            <div
                                v-if="form.errors.reference_range"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.reference_range }}
                            </div>

                        </div>

                        <!-- Display Order -->
                        <div>

                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Display Order
                            </label>

                            <input
                                v-model="form.display_order"
                                type="number"
                                min="1"
                                class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            />

                            <div
                                v-if="form.errors.display_order"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.display_order }}
                            </div>

                        </div>

                        <!-- Active Toggle -->
                        <div>

                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Status
                            </label>

                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"
                            >

                                <div>

                                    <p class="font-medium text-slate-700">
                                        Active Parameter
                                    </p>

                                    <p class="text-sm text-slate-500">
                                        Available for use in laboratory reports.
                                    </p>

                                </div>

                                <!-- Toggle -->
                                <button
                                    type="button"
                                    @click="form.is_active = !form.is_active"
                                    :class="[
                                        'relative inline-flex h-7 w-14 items-center rounded-full transition-all duration-300',
                                        form.is_active
                                            ? 'bg-emerald-600'
                                            : 'bg-slate-300'
                                    ]"
                                >

                                    <span
                                        :class="[
                                            'inline-block h-5 w-5 transform rounded-full bg-white transition-all duration-300',
                                            form.is_active
                                                ? 'translate-x-8'
                                                : 'translate-x-1'
                                        ]"
                                    />

                                </button>

                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div
                        class="mt-8 flex items-center justify-end gap-3 border-t border-slate-200 pt-6"
                    >

                        <Link
                            :href="route('laboratory.test-parameters.index')"
                            class="rounded-xl border border-slate-300 px-5 py-2.5 font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50"
                        >

                            <i class="fas fa-save"></i>

                            {{
                                form.processing
                                    ? 'Saving...'
                                    : 'Save Parameter'
                            }}

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </AuthenticatedLayout>

</template>