<script setup>
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    modelValue: { type: [String, Number], default: '' },
    title: { type: String, required: true },
    placeholder: { type: String, default: 'Tap keys to enter the lot number.' },
    allowSpace: { type: Boolean, default: true },
    maxLength: { type: [Number, String], default: null },
    sessionKey: { type: String, default: '' },
});

const emit = defineEmits(['close', 'closed', 'confirm']);

const rows = [
    ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'],
    ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
    ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
    ['Z', 'X', 'C', 'V', 'B', 'N', 'M', '-'],
];
const draft = ref('');

const displayValue = computed(() => draft.value || '-');
const maxLengthValue = computed(() => {
    if (props.maxLength === null || props.maxLength === '') return null;

    const value = Number(props.maxLength);
    return Number.isFinite(value) && value > 0 ? value : null;
});

const loadDraft = () => {
    draft.value = sanitizeValue(props.modelValue);
};

const sanitizeValue = value => {
    const allowedPattern = props.allowSpace ? /[^A-Z0-9 -]/g : /[^A-Z0-9-]/g;
    const sanitized = String(value ?? '')
        .toUpperCase()
        .replace(allowedPattern, '');

    return maxLengthValue.value === null ? sanitized : sanitized.slice(0, maxLengthValue.value);
};

watch(
    () => props.show,
    show => {
        if (show) loadDraft();
    },
    { immediate: true },
);

const appendKey = key => {
    if (maxLengthValue.value !== null && draft.value.length >= maxLengthValue.value) return;
    draft.value += key;
};

const backspace = () => {
    draft.value = draft.value.slice(0, -1);
};

const clear = () => {
    draft.value = '';
};

const confirm = () => {
    emit('confirm', draft.value);
};

const handleKeydown = event => {
    if (event.ctrlKey || event.altKey || event.metaKey) return;

    if (/^[a-zA-Z0-9-]$/.test(event.key)) {
        event.preventDefault();
        appendKey(event.key.toUpperCase());
    } else if (event.key === ' ' && props.allowSpace) {
        event.preventDefault();
        appendKey(' ');
    } else if (event.key === 'Backspace') {
        event.preventDefault();
        backspace();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        confirm();
    } else if (event.key === 'Escape') {
        event.preventDefault();
        emit('close');
    }
};
</script>

<template>
    <TransitionRoot appear :show="show" as="template" @after-leave="emit('closed')">
        <Dialog as="div" class="relative z-50" @close="emit('close')">
            <TransitionChild
                as="template"
                enter="duration-200 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-150 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-900/60" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto p-4">
                <div class="flex min-h-full items-center justify-center">
                    <TransitionChild
                        as="template"
                        enter="duration-200 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-150 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-3xl rounded-2xl bg-white p-5 shadow-2xl"
                            @keydown="handleKeydown"
                        >
                            <DialogTitle class="text-center text-lg font-semibold text-gray-900">
                                {{ title }}
                            </DialogTitle>
                            <p class="mt-1 text-center text-sm text-gray-500">
                                {{ placeholder }}
                            </p>

                            <div
                                class="mt-4 flex min-h-[4.5rem] items-center justify-end rounded-xl border-2 border-indigo-200 bg-indigo-50 px-4"
                                aria-live="polite"
                            >
                                <span
                                    class="min-w-0 break-all text-right text-3xl font-semibold"
                                    :class="draft ? 'text-gray-900' : 'text-gray-400'"
                                >
                                    {{ displayValue }}
                                </span>
                            </div>

                            <div class="mt-4 space-y-2" aria-label="Tablet QWERTY keyboard">
                                <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
                                <div
                                    v-for="(row, rowIndex) in rows"
                                    :key="rowIndex"
                                    class="grid gap-2"
                                    :class="rowIndex === 2 ? 'grid-cols-9 px-8' : rowIndex === 3 ? 'grid-cols-8 px-14' : 'grid-cols-10'"
                                >
                                    <button
                                        v-for="key in row"
                                        :key="key"
                                        type="button"
                                        class="min-h-[3.75rem] rounded-xl border border-gray-300 bg-white text-xl font-semibold text-gray-900 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-100"
                                        @click="appendKey(key)"
                                    >
                                        {{ key }}
                                    </button>
                                </div>
                                <!-- eslint-enable vue/valid-v-for -->
                            </div>

                            <button
                                v-if="allowSpace"
                                type="button"
                                class="mt-3 min-h-[3.5rem] w-full rounded-xl border border-gray-300 bg-white px-4 text-base font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-100"
                                @click="appendKey(' ')"
                            >
                                Space
                            </button>

                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <button
                                    type="button"
                                    class="min-h-[3.5rem] rounded-xl border border-red-200 bg-red-50 px-4 text-base font-semibold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    @click="clear"
                                >
                                    Clear
                                </button>
                                <button
                                    type="button"
                                    class="min-h-[3.5rem] rounded-xl border border-gray-300 bg-white px-4 text-base font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    @click="backspace"
                                >
                                    Backspace
                                </button>
                                <button
                                    type="button"
                                    class="min-h-[3.5rem] rounded-xl border border-gray-300 bg-white px-4 text-base font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    @click="emit('close')"
                                >
                                    Cancel
                                </button>
                            </div>

                            <button
                                type="button"
                                class="mt-3 min-h-[3.5rem] w-full rounded-xl bg-indigo-600 px-4 text-base font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                @click="confirm"
                            >
                                Done
                            </button>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
