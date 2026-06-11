<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    labOrder: Object,
})

const rows = []

props.labOrder.items.forEach(item => {

    item.lab_test.parameters.forEach(parameter => {

        rows.push({
            lab_order_item_id: item.id,
            lab_test_parameter_id: parameter.id,
            parameter_name: parameter.name,
            unit: parameter.unit,
            reference_range: parameter.reference_range,
            result_value: '',
            remarks: '',
            test_name: item.lab_test.name,
        })
    })
})

const form = useForm({
    results: rows,
})

const submit = () => {
    form.post(
        route(
            'laboratory.orders.results.store',
            props.labOrder.id
        )
    )
}
</script>

<template>

<Head title="Enter Lab Results" />

<AuthenticatedLayout>

    <div class="p-6 max-w-7xl mx-auto">

    <div class="mb-6">

        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Enter Results
        </h1>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ labOrder.order_number }} &middot; {{ labOrder.patient?.name }}
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500">
            Doctor: {{ labOrder.doctor?.name }} &middot; BS: {{ labOrder.created_at_bs || '—' }}
        </p>

    </div>

    <div class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm">

        <div class="border-b dark:border-gray-700 px-6 py-5">

            <h2 class="font-bold text-gray-900 dark:text-gray-100">
                {{ labOrder.patient.name }}
            </h2>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-gray-50 dark:bg-gray-700">

                    <tr>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Test
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Parameter
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Result
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Unit
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                            Reference Range
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <tr
                        v-for="(row,index) in form.results"
                        :key="index"
                        class="border-t dark:border-gray-700"
                    >

                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ row.test_name }}
                        </td>

                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ row.parameter_name }}
                        </td>

                        <td class="px-6 py-4">

                            <input
                                v-model="row.result_value"
                                type="text"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >

                        </td>

                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ row.unit }}
                        </td>

                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                            {{ row.reference_range }}
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-6">

        <button
            @click="submit"
            class="rounded-xl bg-emerald-600 px-6 py-3 text-white"
        >
            Save Results
        </button>

    </div>

</div>

</AuthenticatedLayout>

</template>