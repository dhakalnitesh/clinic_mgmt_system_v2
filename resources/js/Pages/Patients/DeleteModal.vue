<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div
            class="absolute inset-0 bg-black/50"
            @click="$emit('close')"
        ></div>

        <div class="relative bg-white w-full max-w-md rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-red-600">
                    Delete Patient
                </h3>

                <button
                    class="text-gray-500 hover:text-gray-700"
                    @click="$emit('close')"
                >
                    ✕
                </button>
            </div>

            <div class="text-center space-y-3">
                <div class="text-red-600 text-4xl">
                    <i class="mdi mdi-close-circle-outline"></i>
                </div>

                <h3 class="text-lg font-semibold">
                    Are you sure you want to delete
                    <b>{{ item.name }}</b> ?
                </h3>

                <p class="text-sm text-gray-500">
                    This action cannot be undone.
                </p>

                <div class="flex justify-center gap-3 pt-4">
                    <button
                        class="px-4 py-2 rounded-lg border"
                        @click="$emit('close')"
                    >
                        Cancel
                    </button>

                    <button
                        class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white"
                        :disabled="processing"
                        @click="submit"
                    >
                        {{ processing ? 'Deleting...' : 'Proceed' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['close'])

const processing = ref(false)

const submit = () => {
    processing.value = true

    router.delete(route('patients.destroy', props.item.id), {
        preserveScroll: true,

        onFinish: () => {
            processing.value = false
        },

        onSuccess: () => {
            emit('close')
        },
    })
}
</script>