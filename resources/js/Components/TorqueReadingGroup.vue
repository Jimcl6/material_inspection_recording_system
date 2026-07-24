<script setup>
import { computed, nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import NumericKeypadDialog from '@/Components/NumericKeypadDialog.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    period: {
        type: String,
        required: true,
        validator: (value) => ['am', 'pm'].includes(value),
    },
    title: { type: String, required: true },
    time: { type: String, default: '' },
    readings: { type: Array, default: () => [{ torque_value: '' }] },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:time', 'update:readings']);
const maximumReadings = 8;
const showKeypad = ref(false);
const activeReadingIndex = ref(0);
const readingTriggers = ref([]);

const timeValue = computed({
    get: () => props.time,
    set: (value) => emit('update:time', value),
});

const timeError = computed(() => props.errors[`time_${props.period}`]);
const groupError = computed(() => props.errors[`readings.${props.period}`] || props.errors.readings);
const readingError = (index) => props.errors[`readings.${props.period}.${index}.torque_value`];
const activeReading = computed(() => props.readings[activeReadingIndex.value] || { torque_value: '' });
const activeReadingTitle = computed(() => `${props.title} — Torque Reading ${activeReadingIndex.value + 1}`);
const showNextReading = computed(() => activeReadingIndex.value < maximumReadings - 1);

const setReadingTrigger = (element, index) => {
    if (element) readingTriggers.value[index] = element;
};

const openKeypad = (index) => {
    activeReadingIndex.value = index;
    showKeypad.value = true;
};

const closeKeypad = () => {
    showKeypad.value = false;
};

const restoreKeypadFocus = () => {
    const triggerIndex = activeReadingIndex.value;
    nextTick(() => readingTriggers.value[triggerIndex]?.focus());
};

const updateReading = (index, value) => {
    const updated = props.readings.map((reading, readingIndex) => (
        readingIndex === index ? { ...reading, torque_value: value } : reading
    ));
    emit('update:readings', updated);
};

const addReading = () => {
    if (props.readings.length < maximumReadings) {
        emit('update:readings', [...props.readings, { torque_value: '' }]);
    }
};

const removeReading = (index) => {
    const updated = props.readings.filter((_, readingIndex) => readingIndex !== index);
    emit('update:readings', updated.length ? updated : [{ torque_value: '' }]);
};

const confirmReading = (value) => {
    updateReading(activeReadingIndex.value, value);
    closeKeypad();
};

const confirmAndAdvance = (value) => {
    const updated = props.readings.map((reading, readingIndex) => (
        readingIndex === activeReadingIndex.value ? { ...reading, torque_value: value } : reading
    ));
    const nextIndex = activeReadingIndex.value + 1;

    if (nextIndex >= updated.length && updated.length < maximumReadings) {
        updated.push({ torque_value: '' });
    }

    emit('update:readings', updated);
    activeReadingIndex.value = nextIndex;
};
</script>

<template>
    <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h4 class="text-lg font-medium text-gray-900">{{ title }}</h4>
                <p class="mt-1 text-xs text-gray-500">Up to {{ maximumReadings }} torque readings</p>
            </div>
            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-200">
                {{ readings.length }} / {{ maximumReadings }}
            </span>
        </div>

        <div class="mb-5">
            <InputLabel :for="`time_${period}`" value="Time" />
            <TextInput
                :id="`time_${period}`"
                v-model="timeValue"
                type="time"
                class="mt-1 block w-full"
                :class="{ 'border-red-500': timeError }"
            />
            <InputError :message="timeError" class="mt-2" />
        </div>

        <div class="space-y-4">
            <div v-for="(reading, index) in readings" :key="index">
                <div class="flex items-center justify-between gap-3">
                    <InputLabel :for="`${period}_torque_${index}`" :value="`Torque Reading ${index + 1}`" />
                    <button
                        v-if="readings.length > 1"
                        type="button"
                        class="text-xs font-medium text-red-600 hover:text-red-800"
                        @click="removeReading(index)"
                    >
                        Remove
                    </button>
                </div>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <button
                        :id="`${period}_torque_${index}`"
                        :ref="(element) => setReadingTrigger(element, index)"
                        type="button"
                        class="flex min-h-[3.5rem] w-full items-center rounded-md border bg-white px-3 pr-14 text-left shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        :class="readingError(index) ? 'border-red-500' : 'border-gray-300'"
                        aria-haspopup="dialog"
                        @click="openKeypad(index)"
                    >
                        <span :class="reading.torque_value !== '' && reading.torque_value !== null ? 'text-gray-900' : 'text-gray-400'">
                            {{ reading.torque_value !== '' && reading.torque_value !== null ? reading.torque_value : '0.00' }}
                        </span>
                    </button>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">N·m</span>
                    </div>
                </div>
                <InputError :message="readingError(index)" class="mt-2" />
            </div>
        </div>

        <InputError :message="groupError" class="mt-4" />

        <button
            type="button"
            class="mt-5 inline-flex w-full items-center justify-center rounded-md border border-dashed border-indigo-300 bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:border-indigo-400 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="readings.length >= maximumReadings"
            @click="addReading"
        >
            <span v-if="readings.length < maximumReadings">+ Add Torque Reading</span>
            <span v-else>Maximum of 8 readings reached</span>
        </button>

        <NumericKeypadDialog
            :show="showKeypad"
            :model-value="activeReading.torque_value"
            :title="activeReadingTitle"
            unit="N·m"
            :decimal-places="2"
            :max-integer-digits="8"
            :max-value="99999999.99"
            :show-next="showNextReading"
            :session-key="activeReadingIndex"
            @close="closeKeypad"
            @closed="restoreKeypadFocus"
            @confirm="confirmReading"
            @confirm-next="confirmAndAdvance"
        />
    </div>
</template>
