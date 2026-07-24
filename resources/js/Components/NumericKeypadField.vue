<script setup>
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import NumericKeypadDialog from '@/Components/NumericKeypadDialog.vue';

defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    dialogTitle: { type: String, required: true },
    modelValue: { type: [String, Number], default: '' },
    unit: { type: String, default: '' },
    placeholder: { type: String, default: '0' },
    decimalPlaces: { type: Number, default: 0 },
    maxIntegerDigits: { type: Number, default: 18 },
    maxValue: { type: [String, Number], default: null },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);
const showKeypad = ref(false);
const trigger = ref(null);

const closeKeypad = () => {
    showKeypad.value = false;
};

const restoreFocus = () => {
    nextTick(() => trigger.value?.focus());
};

const confirmValue = (value) => {
    emit('update:modelValue', value);
    closeKeypad();
};
</script>

<template>
    <div>
        <InputLabel :for="id" :value="label" />
        <button
            :id="id"
            ref="trigger"
            type="button"
            class="mt-1 flex min-h-[3.5rem] w-full items-center justify-between rounded-md border bg-white px-3 text-left shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
            :class="error ? 'border-red-500' : 'border-gray-300'"
            aria-haspopup="dialog"
            @click="showKeypad = true"
        >
            <span :class="modelValue !== '' && modelValue !== null ? 'text-gray-900' : 'text-gray-400'">
                {{ modelValue !== '' && modelValue !== null ? modelValue : placeholder }}
            </span>
            <span v-if="unit" class="ml-3 shrink-0 text-sm text-gray-500">{{ unit }}</span>
        </button>
        <InputError :message="error" class="mt-2" />

        <NumericKeypadDialog
            :show="showKeypad"
            :model-value="modelValue"
            :title="dialogTitle"
            :unit="unit"
            :decimal-places="decimalPlaces"
            :max-integer-digits="maxIntegerDigits"
            :max-value="maxValue"
            @close="closeKeypad"
            @closed="restoreFocus"
            @confirm="confirmValue"
        />
    </div>
</template>
