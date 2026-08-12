<script setup lang="ts">
import { computed } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    steps: string[];
    modelValue: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const isLast = computed(() => props.modelValue >= props.steps.length - 1);
</script>

<template>
    <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
    <div class="tablet-form-stepper relative z-0 mb-10 rounded-lg border border-indigo-100 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                    Step {{ modelValue + 1 }} of {{ steps.length }}
                </p>
                <p class="break-words text-base font-bold leading-snug text-gray-900">{{ steps[modelValue] }}</p>
            </div>
            <div class="grid w-full grid-cols-2 gap-2 lg:w-auto lg:flex lg:shrink-0">
                <button
                    type="button"
                    :disabled="modelValue === 0"
                    class="inline-flex min-h-11 items-center justify-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 disabled:opacity-40"
                    @click="emit('update:modelValue', modelValue - 1)"
                >
                    <ChevronLeftIcon class="h-5 w-5" /> Back
                </button>
                <button
                    v-if="!isLast"
                    type="button"
                    class="inline-flex min-h-11 items-center justify-center gap-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                    @click="emit('update:modelValue', modelValue + 1)"
                >
                    Next <ChevronRightIcon class="h-5 w-5" />
                </button>
            </div>
        </div>
        <div class="mt-3 flex gap-1.5">
            <span
                v-for="(step, index) in steps"
                :key="`${step}-${index}`"
                class="h-1.5 flex-1 rounded-full"
                :class="index <= modelValue ? 'bg-indigo-600' : 'bg-gray-200'"
            />
        </div>
    </div>
    <!-- eslint-enable vue/valid-v-for -->
</template>
