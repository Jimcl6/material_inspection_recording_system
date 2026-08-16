<script setup lang="ts">
type DuplicateSequenceMode = 'next_letter' | 'same_letter_new_run';

defineProps<{
    show: boolean;
    recordLabel?: string | null;
}>();

const emit = defineEmits<{
    close: [];
    select: [mode: DuplicateSequenceMode];
}>();
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4 py-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="welding-duplicate-sequence-title"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-lg overflow-hidden rounded-lg bg-white shadow-xl">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 id="welding-duplicate-sequence-title" class="text-lg font-semibold text-gray-900">
                    How should this copy be sequenced?
                </h3>
                <p v-if="recordLabel" class="mt-1 text-sm text-gray-500">
                    {{ recordLabel }}
                </p>
            </div>

            <div class="space-y-4 px-6 py-5">
                <p class="text-sm text-gray-600">
                    Choose the tracking pattern for this copied checksheet. You can review the run details on the next screen.
                </p>

                <button
                    type="button"
                    class="w-full rounded-md border border-indigo-200 bg-indigo-50 px-4 py-3 text-left transition hover:border-indigo-300 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    @click="emit('select', 'next_letter')"
                >
                    <span class="block text-sm font-semibold text-indigo-900">Next letter for this run</span>
                    <span class="mt-1 block text-sm text-indigo-700">Keeps Job Number and Prod Qty, then assigns the next Letter Code.</span>
                </button>

                <button
                    type="button"
                    class="w-full rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-left transition hover:border-emerald-300 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    @click="emit('select', 'same_letter_new_run')"
                >
                    <span class="block text-sm font-semibold text-emerald-900">Same letter, new run details</span>
                    <span class="mt-1 block text-sm text-emerald-700">Keeps Letter Code, then clears Job Number and Prod Qty for entry.</span>
                </button>
            </div>

            <div class="flex justify-end border-t border-gray-200 bg-gray-50 px-6 py-4">
                <button
                    type="button"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    @click="emit('close')"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>
