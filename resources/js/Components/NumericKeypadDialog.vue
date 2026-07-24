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
    unit: { type: String, default: '' },
    decimalPlaces: { type: Number, default: 0 },
    maxIntegerDigits: { type: Number, default: 18 },
    maxValue: { type: [String, Number], default: null },
    showNext: { type: Boolean, default: false },
    sessionKey: { type: [String, Number], default: '' },
});

const emit = defineEmits(['close', 'closed', 'confirm', 'confirm-next']);

const keypadDigits = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];
const draft = ref('');
const initialWasNonNumeric = ref(false);

const numericPattern = computed(() => {
    if (props.decimalPlaces > 0) {
        return new RegExp(`^\\d+(?:\\.\\d{1,${props.decimalPlaces}})?$`);
    }

    return /^\d+$/;
});

const isCompleteValue = computed(() => {
    if (!draft.value || !numericPattern.value.test(draft.value)) return false;

    return props.maxValue === null || Number(draft.value) <= Number(props.maxValue);
});

const canConfirm = computed(() => draft.value === '' || isCompleteValue.value);
const canAdvance = computed(() => isCompleteValue.value);

const validationMessage = computed(() => {
    if (!draft.value) return '';

    if (draft.value.endsWith('.')) {
        return 'Enter a digit after the decimal point.';
    }

    const [, decimalPart = ''] = draft.value.split('.');
    if (decimalPart.length > props.decimalPlaces) {
        return `Use no more than ${props.decimalPlaces} decimal place${props.decimalPlaces === 1 ? '' : 's'}.`;
    }

    if (props.maxValue !== null && Number(draft.value) > Number(props.maxValue)) {
        return `The maximum allowed value is ${props.maxValue}.`;
    }

    return '';
});

const loadDraft = () => {
    const initialValue = String(props.modelValue ?? '').trim();

    if (initialValue === '') {
        draft.value = '';
        initialWasNonNumeric.value = false;
        return;
    }

    if (/^\d+(?:\.\d*)?$/.test(initialValue)) {
        draft.value = initialValue;
        initialWasNonNumeric.value = false;
        return;
    }

    draft.value = '';
    initialWasNonNumeric.value = true;
};

watch(
    () => [props.show, props.sessionKey],
    ([show]) => {
        if (show) loadDraft();
    },
    { immediate: true },
);

const appendDigit = (digit) => {
    const [integerPart, decimalPart] = draft.value.split('.');

    if (draft.value.includes('.')) {
        if ((decimalPart || '').length >= props.decimalPlaces) return;
        draft.value += digit;
        return;
    }

    if (draft.value === '0') {
        draft.value = digit;
        return;
    }

    if ((integerPart || '').length >= props.maxIntegerDigits) return;
    draft.value += digit;
};

const appendDecimal = () => {
    if (props.decimalPlaces < 1 || draft.value.includes('.')) return;
    draft.value = draft.value ? `${draft.value}.` : '0.';
};

const backspace = () => {
    draft.value = draft.value.slice(0, -1);
};

const clear = () => {
    draft.value = '';
    initialWasNonNumeric.value = false;
};

const confirm = () => {
    if (canConfirm.value) emit('confirm', draft.value);
};

const confirmNext = () => {
    if (canAdvance.value) emit('confirm-next', draft.value);
};

const handleKeydown = (event) => {
    if (event.ctrlKey || event.altKey || event.metaKey) return;

    if (/^\d$/.test(event.key)) {
        event.preventDefault();
        appendDigit(event.key);
    } else if (event.key === '.') {
        event.preventDefault();
        appendDecimal();
    } else if (event.key === 'Backspace') {
        event.preventDefault();
        backspace();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        confirm();
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
                            class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl"
                            @keydown="handleKeydown"
                        >
                            <DialogTitle class="text-center text-lg font-semibold text-gray-900">
                                {{ title }}
                            </DialogTitle>
                            <p class="mt-1 text-center text-sm text-gray-500">
                                Tap the keypad to enter the reading.
                            </p>

                            <div
                                class="mt-4 flex min-h-[4.5rem] items-center justify-end rounded-xl border-2 border-indigo-200 bg-indigo-50 px-4"
                                aria-live="polite"
                            >
                                <span
                                    class="min-w-0 break-all text-right text-3xl font-semibold tracking-wide"
                                    :class="draft ? 'text-gray-900' : 'text-gray-400'"
                                >
                                    {{ draft || '—' }}
                                </span>
                                <span v-if="unit" class="ml-2 shrink-0 text-lg font-medium text-gray-500">
                                    {{ unit }}
                                </span>
                            </div>

                            <p v-if="initialWasNonNumeric" class="mt-2 text-sm text-amber-700">
                                The saved value is not numeric. Enter a numeric replacement or choose Cancel.
                            </p>
                            <p v-else-if="validationMessage" class="mt-2 text-sm text-red-600">
                                {{ validationMessage }}
                            </p>

                            <div class="mt-4 grid grid-cols-3 gap-2" aria-label="Numeric keypad">
                                <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
                                <button
                                    v-for="digit in keypadDigits"
                                    :key="digit"
                                    type="button"
                                    class="min-h-[4rem] rounded-xl border border-gray-300 bg-white text-2xl font-semibold text-gray-900 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-100"
                                    @click="appendDigit(digit)"
                                >
                                    {{ digit }}
                                </button>
                                <!-- eslint-enable vue/valid-v-for -->
                                <button
                                    type="button"
                                    class="min-h-[4rem] rounded-xl border border-gray-300 bg-gray-50 text-2xl font-semibold text-gray-900 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="decimalPlaces < 1"
                                    aria-label="Decimal point"
                                    @click="appendDecimal"
                                >
                                    .
                                </button>
                                <button
                                    type="button"
                                    class="min-h-[4rem] rounded-xl border border-gray-300 bg-white text-2xl font-semibold text-gray-900 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-100"
                                    @click="appendDigit('0')"
                                >
                                    0
                                </button>
                                <button
                                    type="button"
                                    class="min-h-[4rem] rounded-xl border border-gray-300 bg-gray-50 text-2xl font-semibold text-gray-900 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    aria-label="Backspace"
                                    @click="backspace"
                                >
                                    ⌫
                                </button>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
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
                                    @click="emit('close')"
                                >
                                    Cancel
                                </button>
                            </div>

                            <div class="mt-3 grid gap-3" :class="showNext ? 'grid-cols-2' : 'grid-cols-1'">
                                <button
                                    type="button"
                                    class="min-h-[3.5rem] rounded-xl bg-indigo-600 px-4 text-base font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="!canConfirm"
                                    @click="confirm"
                                >
                                    Done
                                </button>
                                <button
                                    v-if="showNext"
                                    type="button"
                                    class="min-h-[3.5rem] rounded-xl bg-emerald-600 px-4 text-base font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="!canAdvance"
                                    @click="confirmNext"
                                >
                                    Next Reading
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
