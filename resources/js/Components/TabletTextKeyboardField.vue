<script setup>
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TabletTextKeyboardDialog from '@/Components/TabletTextKeyboardDialog.vue';

defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    dialogTitle: { type: String, required: true },
    modelValue: { type: [String, Number], default: '' },
    placeholder: { type: String, default: 'Tap to enter' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'blur']);
const showKeyboard = ref(false);
const trigger = ref(null);

const closeKeyboard = () => {
    showKeyboard.value = false;
};

const restoreFocus = () => {
    nextTick(() => trigger.value?.focus());
};

const confirmValue = value => {
    emit('update:modelValue', value);
    emit('blur');
    closeKeyboard();
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
            @click="showKeyboard = true"
        >
            <span :class="modelValue !== '' && modelValue !== null ? 'text-gray-900' : 'text-gray-400'">
                {{ modelValue !== '' && modelValue !== null ? modelValue : placeholder }}
            </span>
        </button>
        <InputError :message="error" class="mt-2" />

        <TabletTextKeyboardDialog
            :show="showKeyboard"
            :model-value="modelValue"
            :title="dialogTitle"
            @close="closeKeyboard"
            @closed="restoreFocus"
            @confirm="confirmValue"
        />
    </div>
</template>
