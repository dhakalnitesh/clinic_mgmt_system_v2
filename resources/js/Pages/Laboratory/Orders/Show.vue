<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    labOrder: Object,
})

const updateStatus = (routeName) => {
    router.patch(route(routeName, props.labOrder.id))
}

const statusClass = (status) => {
    switch (status) {
        case 'ordered':
            return 'bg-amber-100 text-amber-700'

        case 'sample_collected':
            return 'bg-blue-100 text-blue-700'

        case 'processing':
            return 'bg-purple-100 text-purple-700'

        case 'completed':
            return 'bg-emerald-100 text-emerald-700'

        case 'cancelled':
            return 'bg-red-100 text-red-700'

        default:
            return 'bg-gray-100 text-gray-700'
    }
}
</script>   
<template>

<Head :title="labOrder.order_number" />

<AuthenticatedLayout>

    <div class="p-6">

    <!-- HEADER -->

    <div
        class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
    >

        <div>

            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ labOrder.order_number }}
            </h1>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Laboratory Order Details
            </p>

        </div>

        <span
            class="rounded-full px-4 py-2 text-sm font-semibold"
            :class="statusClass(labOrder.status)"
        >
            {{ labOrder.status.replaceAll('_', ' ') }}
        </span>

    </div>

    <!-- INFO GRID -->

    <div class="mb-6 grid gap-6 lg:grid-cols-2">

        <!-- PATIENT -->

        <div
            class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-6 shadow-sm"
        >

            <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                Patient Information
            </h2>

            <div class="space-y-2 text-gray-700 dark:text-gray-300">

                <div>
                    <strong>Name:</strong>
                    {{ labOrder.patient?.name }}
                </div>

                <div>
                    <strong>UHID:</strong>
                    {{ labOrder.patient?.uhid }}
                </div>

                <div>
                    <strong>Gender:</strong>
                    {{ labOrder.patient?.gender }}
                </div>

                <div>
                    <strong>Age:</strong>
                    {{ labOrder.patient?.age }}
                </div>

            </div>

        </div>

        <!-- ORDER -->

        <div
            class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-6 shadow-sm"
        >

            <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                Order Information
            </h2>

            <div class="space-y-2 text-gray-700 dark:text-gray-300">

                <div>
                    <strong>Order Number:</strong>
                    {{ labOrder.order_number }}
                </div>

                <div>
                    <strong>Doctor:</strong>
                    {{ labOrder.doctor?.name }}
                </div>

                <div>
                    <strong>Date:</strong>

                    {{
                        new Date(
                            labOrder.created_at
                        ).toLocaleDateString()
                    }}

                    <span class="text-gray-400">(BS: {{ labOrder.created_at_bs || '—' }})</span>

                </div>

            </div>

        </div>

    </div>

    <!-- TESTS -->

    <div
        class="mb-6 rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm"
    >

        <div class="border-b dark:border-gray-700 px-6 py-5">

            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                Requested Tests
            </h2>

        </div>

        <div class="divide-y dark:divide-gray-700">

            <div
                v-for="item in labOrder.items"
                :key="item.id"
                class="flex items-center justify-between p-5"
            >

                <div>

                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ item.lab_test?.name }}
                    </div>

                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ item.lab_test?.code }}
                    </div>

                </div>

                <span
                    class="rounded-full bg-indigo-100 px-3 py-1 text-xs text-indigo-700"
                >
                    Test Requested
                </span>

            </div>

        </div>

    </div>

    <!-- WORKFLOW -->

    <div
        class="rounded-3xl border bg-white dark:bg-gray-800 dark:border-gray-700 p-6 shadow-sm"
    >

        <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
            Workflow Actions
        </h2>

        <div class="flex flex-wrap gap-3">

            <button
                v-if="labOrder.status === 'ordered'"
                @click="updateStatus('laboratory.orders.collect-sample')"
                class="rounded-xl bg-indigo-600 px-5 py-3 text-white"
            >
                Collect Sample
            </button>

            <button
                v-if="labOrder.status === 'sample_collected'"
                @click="updateStatus('laboratory.orders.start-processing')"
                class="rounded-xl bg-purple-600 px-5 py-3 text-white"
            >
                Start Processing
            </button>

            <button
                v-if="labOrder.status === 'processing'"
                @click="updateStatus('laboratory.orders.complete')"
                class="rounded-xl bg-emerald-600 px-5 py-3 text-white"
            >
                Complete Order
            </button>

            <Link
                :href="route('laboratory.orders.index')"
                class="rounded-xl border px-5 py-3"
            >
                Back to Orders
            </Link>

        </div>

    </div>

</div>

</AuthenticatedLayout>

</template>